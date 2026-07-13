# UniFi API-key authentication

The connector supports two UniFi authentication modes:

* **Username and password** use the existing `art-of-wifi/unifi-api-client`
  cookie-login implementation.
* **API key** sends `X-API-KEY` to the UniFi OS Network proxy API.  This mode
  does not perform a controller login and uses the same Network endpoints as
  the password mode.

The administration form selects the mode. JavaScript immediately shows the
API-key field or the username/password fields for the selected mode; the
server validates the selected credentials as well, so the configuration does
not rely on JavaScript.

The selected secret is stored only in
`/var/lib/iserv/unificonnector/config.json`. Existing configuration files that
do not contain an authentication mode continue to use username/password.
