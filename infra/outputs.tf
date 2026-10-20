# Values Terraform prints after apply; later used to wire the app to the database

output "database_host" {
  description = "Direct (non-pooled) host; migrations run best without the pooler"
  value       = neon_project.athenaeum.database_host
}

output "database_name" {
  value = neon_project.athenaeum.database_name
}

output "database_user" {
  value = neon_project.athenaeum.database_user
}

output "app_url" {
  description = "Azure's default address for the app (before the custom domain)"
  value       = "https://${azurerm_container_app.app.ingress[0].fqdn}"
}

output "admin_password" {
  value     = random_password.admin.result
  sensitive = true
}

output "github_azure_client_id" {
  value = azurerm_user_assigned_identity.github_deploy.client_id
}

output "github_azure_tenant_id" {
  value = data.azurerm_client_config.current.tenant_id
}

output "github_azure_subscription_id" {
  value = data.azurerm_client_config.current.subscription_id
}