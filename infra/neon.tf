# Neon Project was already created that's why we need to tell
# Terraform and adopt it, no need to create new resource we need to adopt


resource "neon_project" "athenaeum" {
  name                      = "athenaeum"
  org_id                    = "org-tiny-moon-80256843"
  region_id                 = "aws-ap-southeast-1"
  pg_version                = 17
  history_retention_seconds = 21600

lifecycle {
    prevent_destroy = true
  }
}
