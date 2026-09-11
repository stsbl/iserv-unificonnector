#!/bin/bash

cat <<'EOT'
Test "remove incorrect iserv-stsbl-iserv-unificonnector system user"
  ! getent passwd iserv-stsbl-iserv-unificonnector
  ---
  find /var/lib/iserv/unificonnector /var/log/iserv/unificonnector /var/cache/iserv/unificonnector -xdev -user iserv-stsbl-iserv-unificonnector -exec chown stsbl-iserv-unificonnector:stsbl-iserv-unificonnector {} +
  userdel iserv-stsbl-iserv-unificonnector ||:
  groupdel iserv-stsbl-iserv-unificonnector ||:

EOT
