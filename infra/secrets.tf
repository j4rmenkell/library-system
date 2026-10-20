# Generated once and kept in the (encrypted) state, so they never change
# between plans and never have to be typed or committed

# Laravel's APP_KEY: 32 random bytes, base64-encoded
resource "random_bytes" "app_key" {
  length = 32
}

resource "random_password" "admin" {
  length  = 24
  special = false
}