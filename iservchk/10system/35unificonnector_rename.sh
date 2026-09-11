#!/bin/bash

cat <<'EOT'
Test "migrate iserv-unificonnector system user"
  ! getent passwd iserv-unificonnector || getent passwd stsbl-iserv-unificonnector
  ---
  usermod --prefix / --login stsbl-iserv-unificonnector iserv-unificonnector
  groupmod --prefix / --new-name stsbl-iserv-unificonnector iserv-unificonnector
  usermod --prefix / --gid stsbl-iserv-unificonnector stsbl-iserv-unificonnector

EOT
