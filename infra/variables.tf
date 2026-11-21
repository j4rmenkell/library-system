variable "admin_email" {
  description = "Email of the production admin account"
  type        = string
}

variable "mail_username" {
  description = "Gmail address that sends the app's emails (also the From address)"
  type        = string
}

variable "mail_password" {
  description = "Google App Password for mail_username, not the account password"
  type        = string
  sensitive   = true
}

variable "image_tag" {
  description = "Image to start with; after this, Github Actions deploys new tags"
  type        = string
  default     = "sha-ad55258"
}