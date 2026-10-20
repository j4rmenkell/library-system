terraform {
  required_version = ">= 1.16"

  cloud {
    organization = "j4rmenkell"

    workspaces {
      name = "athenaeum"
    }
  }

  required_providers {
    azurerm = {
      source  = "hashicorp/azurerm"
      version = "~> 5.10"
    }

    cloudflare = {
      source  = "cloudflare/cloudflare"
      version = "~> 5.28"
    }

    neon = {
      source  = "kislerdm/neon"
      version = "~> 0.18.0"
    }
    random = {
      source  = "hashicorp/random"
      version = "~> 3.9"
    }
  }

}