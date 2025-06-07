variable "aws_region" {
  description = "AWS region"
  type        = string
  default     = "us-east-1"
}

variable "ecr_repo_name" {
  description = "Nombre del repositorio ECR"
  type        = string
  default     = "matriculador"
}

variable "image_tag" {
  description = "Tag de la imagen Docker a generar"
  type        = string
  default     = "latest"
}

variable "cluster_name" {
  description = "Nombre del ECS cluster"
  type        = string
  default     = "asterisk-cluster"
}

variable "service_name" {
  description = "Nombre del ECS service"
  type        = string
  default     = "asterisk-service"
}

variable "desired_count" {
  description = "Nº de tareas Fargate a lanzar"
  type        = number
  default     = 1
}
