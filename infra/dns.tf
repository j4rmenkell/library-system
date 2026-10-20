locals {
  cloudflare_zone_id = "c677ede84369dadc473a625c45410b78" # j4rmen.me
}

# library.j4rmen.me points at the app's Azure address.
# proxied = false ("grey cloud"): Azure must see the CNAME to validate the
# domain and issue the certificate, and it terminates HTTPS itself
resource "cloudflare_dns_record" "app" {
  zone_id = local.cloudflare_zone_id
  name    = local.domain
  type    = "CNAME"
  content = azurerm_container_app.app.ingress[0].fqdn
  ttl     = 1
  proxied = false
  comment = "Athenaeum on Azure Container Apps (managed by Terraform)"
}

# Proves to Azure that we own the domain
resource "cloudflare_dns_record" "app_verification" {
  zone_id = local.cloudflare_zone_id
  name    = "asuid.${local.domain}"
  type    = "TXT"
  content = azurerm_container_app.app.custom_domain_verification_id
  ttl     = 1
  comment = "Azure Container Apps domain verification (managed by Terraform)"
}

resource "azurerm_container_app_custom_domain" "app" {
  name             = local.domain
  container_app_id = azurerm_container_app.app.id

  # Azure checks both records when the hostname is added
  depends_on = [
    cloudflare_dns_record.app,
    cloudflare_dns_record.app_verification,
  ]

  lifecycle {
    # The managed certificate is bound by Azure outside Terraform
    # (az containerapp hostname bind); don't undo it on the next apply
    ignore_changes = [certificate_binding_type, container_app_environment_certificate_id]
  }
}