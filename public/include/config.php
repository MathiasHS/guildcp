<?php namespace GuildCP;

require_once __DIR__ . "/classes/config.class.php";

Config::set("site.development", getenv("GCP_DEV"));
Config::set("password.algo", PASSWORD_BCRYPT);
Config::set("password.cost", 12);

Config::set("cookie.session.path", "/");
Config::set("cookie.session.domain", getenv('GCP_COOKIE_DOMAIN'));
Config::set("cookie.session", "gcp_login");
Config::set("cookie.accept_cookies", "gcp_cookies");
Config::set("cookie.secure", !getenv("GCP_DEV"));

Config::set("blizzard.client.id", getenv("GCP_CLIENT_ID"));
Config::set("blizzard.client.secret", getenv("GCP_CLIENT_SECRET"));
Config::set("blizzard.redirect.uri", getenv("GCP_REDIRECT_URI"));
