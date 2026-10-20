variable "admin_email" {
  description = "Email of the production admin account"
  type        = string
}

variable "image_tag" {
  description = "Image to start with; after this, Github Actions deploys new tags"
  type        = string
  default     = "sha-ad55258"
}