output "ecr_repo_url" {
  value = aws_ecr_repository.matriculador.repository_url
}

output "ecs_cluster_name" {
  value = aws_ecs_cluster.cluster.name
}

output "ecs_service_name" {
  value = aws_ecs_service.asterisk.name
}

output "security_group_id" {
  value = aws_security_group.asterisk_sg.id
}
