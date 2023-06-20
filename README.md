# Project Manager

![CI](https://github.com/n3wborn/project-manager/workflows/CI/badge.svg)

1. What ?

    - admin side: a CRUD for projects I did, or the ones I'm currently working on
    - user side: a way to show my work
    - a Symfony 6.3 / Vite / Caddy (/ React ?) stack based on [n3wborn/symfo6.2-vite-caddy](https://github.com/n3wborn/symfo6.2-vite-caddy)

2. Why ?

    - it can be useful when it comes to resume my own previous/current projects
    - I can learn new stuff and improve what I already know
    - it can be fun

## Install

```sh
git clone https://github.com/n3wborn/project-manager.git
cd project-manager
make build && make up
```

For HTTPS to be fully operational **you need to accept certificates on "both sides"**.

1. Go to the main url of the project (https://project-manager.localhost/) and accept certificate
2. Got to the url used by vite (https://node.project-manager.localhost:5173/) and accept certificate

Or, simply follow [TLS certificates](docs/tls.md) doc:

```bash
# Mac
$ docker cp $(docker compose ps -q caddy):/data/caddy/pki/authorities/local/root.crt /tmp/root.crt && sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain /tmp/root.crt
# Linux
$ docker cp $(docker compose ps -q caddy):/data/caddy/pki/authorities/local/root.crt /usr/local/share/ca-certificates/root.crt && sudo update-ca-certificates
# Windows
$ docker compose cp caddy:/data/caddy/pki/authorities/local/root.crt %TEMP%/root.crt && certutil -addstore -f "ROOT" %TEMP%/root.crt
```

Now everything should be fine.

Docker stack is based on [dunglas/symfony-docker](https://github.com/dunglas/symfony-docker).
More infos can be found [here](docs/README.md)

