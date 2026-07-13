# UniFi API-key authentication implementation plan

**Goal:** Let the UniFi Connector synchronize clients through a UniFi API key
without breaking existing username/password installations.

1. Extend the connection settings and persisted configuration with an
   authentication mode and API key. Default absent legacy configuration to
   `password`.
2. Update the administration form and template; load a small JavaScript asset
   that toggles the irrelevant credential rows. Validate credentials on the
   server according to the selected mode.
3. Add an API-key client that calls the UniFi OS Network proxy endpoints with
   `X-API-KEY`, and select it from the existing repository interfaces.
4. Add focused tests for configuration compatibility, form validation, and API
   request authentication. Run PHP quality tools, frontend build, and PHPUnit.
