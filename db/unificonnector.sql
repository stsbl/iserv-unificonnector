CREATE USER "iserv-unificonnector";

CREATE TABLE unificonnector_usergroup (
    id              TEXT PRIMARY KEY,
    name            TEXT NOT NULL,
    priority        INT  NOT NULL UNIQUE
);

CREATE TABLE unificonnector_usergroup_group (
    usergroup_id    TEXT NOT NULL REFERENCES unificonnector_usergroup(id),
    group_uuid      UUID NOT NULL,
    PRIMARY KEY (usergroup_id, group_uuid)
);

CREATE TABLE unificonnector_usergroup_user (
    usergroup_id    TEXT NOT NULL REFERENCES unificonnector_usergroup(id),
    user_uuid       UUID NOT NULL,
    PRIMARY KEY (usergroup_id, user_uuid)
);

CREATE TABLE unificonnector_usergroup_role (
    usergroup_id    TEXT NOT NULL REFERENCES unificonnector_usergroup(id),
    role_uuid       UUID NOT NULL,
    PRIMARY KEY (usergroup_id, role_uuid)
);

CREATE INDEX unificonnector_usergroup_group_group_uuid_key ON unificonnector_usergroup_group (group_uuid);
CREATE INDEX unificonnector_usergroup_user_user_uuid_key ON unificonnector_usergroup_user (user_uuid);
CREATE INDEX unificonnector_usergroup_role_role_uuid_key ON unificonnector_usergroup_role (role_uuid);

GRANT SELECT, INSERT, UPDATE, DELETE ON unificonnector_usergroup, unificonnector_usergroup_group, unificonnector_usergroup_user, unificonnector_usergroup_role TO "iserv-unificonnector";
