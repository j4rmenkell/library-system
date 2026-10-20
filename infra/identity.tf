# Reads the tenant and subscription that `az login` is using
data "azurerm_client_config" "current" {}

# The identity GitHub Actions acts as when it deploys
resource "azurerm_user_assigned_identity" "github_deploy" {
  name                = "id-athenaeum-github"
  location            = azurerm_resource_group.main.location
  resource_group_name = azurerm_resource_group.main.name
  tags                = local.tags
}

# Trust GitHub's OIDC tokens, but only from the main branch of this repo.
# Forks, other branches and pull requests can't log in as this identity
resource "azurerm_federated_identity_credential" "github_main" {
  name                      = "github-main"
  user_assigned_identity_id = azurerm_user_assigned_identity.github_deploy.id
  issuer                    = "https://token.actions.githubusercontent.com"
  audience                  = ["api://AzureADTokenExchange"]
  subject                   = "repo:j4rmenkell@171651517/library-system@1418421623:ref:refs/heads/main"
}

# Least privilege: Contributor on this project's resource group only,
# so a compromised workflow can't touch AquaScale or anything else
resource "azurerm_role_assignment" "github_deploy" {
  scope                = azurerm_resource_group.main.id
  role_definition_name = "Contributor"
  principal_id         = azurerm_user_assigned_identity.github_deploy.principal_id
  principal_type       = "ServicePrincipal"
}