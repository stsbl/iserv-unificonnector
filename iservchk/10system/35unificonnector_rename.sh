#!/bin/bash

cat <<'EOT'
Test "migrate iserv-unificonnector system user"
  ! getent passwd iserv-unificonnector || getent passwd stsbl-iserv-unificonnector
  ---
  groupmod -n stsbl-iserv-unificonnector iserv-unificonnector
  usermod -l stsbl-iserv-unificonnector -g stsbl-iserv-unificonnector iserv-unificonnector

EOT
