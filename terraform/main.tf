
############################
# provider
############################
provider "aws" {
  region = var.aws_region
}

############################
# ECR repository
############################
resource "aws_ecr_repository" "matriculador" {
  name                 = var.ecr_repo_name
  image_tag_mutability = "MUTABLE"
  force_delete         = true
}

############################
# Build & push Docker image
############################
# Autenticación contra ECR para el provider Docker

data "aws_ecr_authorization_token" "auth" {}

provider "docker" {
  registry_auth {
    address  = data.aws_ecr_authorization_token.auth.proxy_endpoint
    username = data.aws_ecr_authorization_token.auth.user_name
    password = data.aws_ecr_authorization_token.auth.password
  }
}

# Construye la imagen con contexto en la raíz del proyecto
resource "docker_image" "matriculador" {
  name         = "${aws_ecr_repository.matriculador.repository_url}:${var.image_tag}"
  build {
    context    = "${path.module}/.."  # ruta al Dockerfile
    dockerfile = "Dockerfile"
  }
  # Mantén un hash para forzar rebuild si cambia Dockerfile
  triggers = {
    build_id = filesha256("${path.module}/../Dockerfile")
  }
}

############################
# IAM para ECS/Fargate
############################
# Execution Role (pull de imagen y logs)
resource "aws_iam_role" "ecs_exec" {
  name               = "ecsTaskExecutionRole-matriculador"
  assume_role_policy = data.aws_iam_policy_document.ecs_tassume.json
}

data "aws_iam_policy_document" "ecs_tassume" {
  statement {
    actions = ["sts:AssumeRole"]
    principals {
      type        = "Service"
      identifiers = ["ecs-tasks.amazonaws.com"]
    }
  }
}

resource "aws_iam_role_policy_attachment" "ecs_exec_attach1" {
  role       = aws_iam_role.ecs_exec.name
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy"
}

############################
# Networking (usar VPC por defecto)
############################
# Obtén la VPC y subredes públicas predeterminadas

data "aws_vpc" "default" {
  default = true
}

data "aws_subnets" "public" {
  filter {
    name   = "vpc-id"
    values = [data.aws_vpc.default.id]
  }
}

############################
# Security Group para SIP/RTP
############################
resource "aws_security_group" "asterisk_sg" {
  name        = "asterisk-sg"
  description = "Permite SIP (5060 UDP) y RTP (10000-20000 UDP)"
  vpc_id      = data.aws_vpc.default.id

  ingress {
    description = "SIP UDP"
    from_port   = 5060
    to_port     = 5060
    protocol    = "udp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    description = "RTP UDP"
    from_port   = 10000
    to_port     = 20000
    protocol    = "udp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

############################
# ECS Cluster
############################
resource "aws_ecs_cluster" "cluster" {
  name = var.cluster_name
}

############################
# Task Definition (Fargate)
############################
resource "aws_ecs_task_definition" "asterisk" {
  family                   = "asterisk-task"
  network_mode             = "awsvpc"
  requires_compatibilities = ["FARGATE"]
  cpu                      = "512"
  memory                   = "1024"
  execution_role_arn       = aws_iam_role.ecs_exec.arn

  container_definitions = jsonencode([
    {
      name      = "asterisk"
      image     = "${aws_ecr_repository.matriculador.repository_url}:${var.image_tag}"
      portMappings = [
        {
          containerPort = 5060
          protocol      = "udp"
        },
        {
          containerPort = 10000
          protocol      = "udp"
        }
      ]
      essential = true
      environment = [
        { name = "TZ", value = "America/Bogota" }
      ]
      linuxParameters = {
        initProcessEnabled = true
      }
    }
  ])
}

############################
# ECS Service
############################
resource "aws_ecs_service" "asterisk" {
  name            = var.service_name
  cluster         = aws_ecs_cluster.cluster.id
  task_definition = aws_ecs_task_definition.asterisk.arn
  desired_count   = var.desired_count
  launch_type     = "FARGATE"

  network_configuration {
    subnets         = data.aws_subnets.public.ids
    security_groups = [aws_security_group.asterisk_sg.id]
    assign_public_ip = true
  }

  lifecycle {
    ignore_changes = [task_definition]
  }
}

