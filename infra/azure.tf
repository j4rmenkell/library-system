locals {
  location = "southeastasia"
  domain   = "library.j4rmen.me"

  # Tags on every Azure resource: who owns it and what it's for
  tags = {
    project    = "athenaeum"
    managed_by = "terraform"
  }
}

resource "azurerm_resource_group" "main" {
  name     = "rg-athenaeum"
  location = local.location
  tags     = local.tags
}

# The environment the app runs in. With no logs_destination, logs are only
# streamed (az containerapp logs show), which keeps it free


resource "azurerm_container_app_environment" "main" {
  name                = "cae-athenaeum"
  location            = azurerm_resource_group.main.location
  resource_group_name = azurerm_resource_group.main.name
  tags                = local.tags
  
  workload_profile {
    name                  = "Consumption"
    workload_profile_type = "Consumption"
  }
}

resource "azurerm_container_app" "app" {
  name                         = "ca-athenaeum"
  container_app_environment_id = azurerm_container_app_environment.main.id
  resource_group_name          = azurerm_resource_group.main.name
  revision_mode                = "Single"
  workload_profile_name        = "Consumption"
  tags                         = local.tags

  # Stored encrypted by Azure; containers only see them through env vars
  secret {
    name  = "app-key"
    value = "base64:${random_bytes.app_key.base64}"
  }

  secret {
    name  = "db-password"
    value = neon_project.athenaeum.database_password
  }

  secret {
    name  = "admin-password"
    value = random_password.admin.result
  }

  ingress {
    external_enabled = true
    target_port      = 8080

    traffic_weight {
      latest_revision = true
      percentage      = 100
    }
  }

  template {
    # Scale to zero when idle; one copy at most, because migrations run on start-up
    min_replicas = 0
    max_replicas = 1

    container {
      name   = "app"
      image  = "ghcr.io/j4rmenkell/library-system:${var.image_tag}"
      cpu    = 0.25
      memory = "0.5Gi"

      env {
        name  = "APP_NAME"
        value = "Marguerite's Athenaeum"
      }
      env {
        name  = "APP_ENV"
        value = "production"
      }
      env {
        name  = "APP_DEBUG"
        value = "false"
      }
      env {
        name  = "APP_URL"
        value = "https://${local.domain}"
      }
      env {
        name        = "APP_KEY"
        secret_name = "app-key"
      }
      env {
        name  = "LOG_CHANNEL"
        value = "stderr"
      }

      env {
        name  = "DB_CONNECTION"
        value = "pgsql"
      }
      env {
        name  = "DB_HOST"
        value = neon_project.athenaeum.database_host
      }
      env {
        name  = "DB_PORT"
        value = "5432"
      }
      env {
        name  = "DB_DATABASE"
        value = neon_project.athenaeum.database_name
      }
      env {
        name  = "DB_USERNAME"
        value = neon_project.athenaeum.database_user
      }
      env {
        name        = "DB_PASSWORD"
        secret_name = "db-password"
      }
      env {
        name  = "DB_SSLMODE"
        value = "require"
      }

      env {
        name  = "SESSION_DRIVER"
        value = "database"
      }
      env {
        name  = "SESSION_SECURE_COOKIE"
        value = "true"
      }
      env {
        name  = "CACHE_STORE"
        value = "database"
      }
      env {
        name  = "QUEUE_CONNECTION"
        value = "sync"
      }
      env {
        name  = "MAIL_MAILER"
        value = "log"
      }

      env {
        name  = "ADMIN_EMAIL"
        value = var.admin_email
      }
      env {
        name        = "ADMIN_PASSWORD"
        secret_name = "admin-password"
      }

      # Laravel's built-in health route; a failing app gets restarted
      liveness_probe {
        transport = "HTTP"
        port      = 8080
        path      = "/up"
      }

      # Start-up runs migrations against Neon, which may be waking up,
      # so allow up to 5 minutes (30 checks x 10 s) before giving up
      startup_probe {
        transport               = "HTTP"
        port                    = 8080
        path                    = "/up"
        interval_seconds        = 10
        failure_count_threshold = 30
      }
    }
  }

  lifecycle {
    # GitHub Actions deploys new image tags (3.7); without this, every
    # terraform apply would roll the app back to var.image_tag
    ignore_changes = [template[0].container[0].image]
  }
}