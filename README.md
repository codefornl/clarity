# clarity
Clarity Case Studies (https://clarity.codefor.nl) is a project to create and share curated sets (cbases) of use cases of civic tech and gov tech. It runs on top of the cbase API at https://cbase.codefor.nl, which is an open HAL complient API.

Please note that the live application is currently running branch "non_dockered_version".

## environment variables
BASE_URI : url of the cbase api

## Run a development environment

The development environment will connect to the cbase.codefor.nl BASE_URI by default. Override it if you have a local setup for the API too.

```
docker build .
docker run -v <localdir>/public:/var/www/html/public -v <localdir>/private:/var/www/html/private -p 8080:80 <your built image>
```

You can then access the site at http://localhost:8080 and make changes in your local php files.
