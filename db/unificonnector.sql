CREATE USER unificonnector;

CREATE TABLE unificonnector_usergroup (
    id              TEXT PRIMARY KEY,
    name            TEXT NOT NULL
);

CREATE TABLE unificonnector_usergroup_group (
    usergroup_id    TEXT NOT NULL REFERENCES unificonnector_usergroup(id),
    group_uuid      UUID NOT NULL REFERENCES groups(uuid),
    PRIMARY KEY (usergroup_id, group_uuid)
);

CREATE TABLE unificonnector_usergroup_role (
    usergroup_id    TEXT NOT NULL REFERENCES unificonnector_usergroup(id),
    role_uuid       UUID NOT NULL REFERENCES security_roles(uuid),
    PRIMARY KEY (usergroup_id, group_uuid)
);
