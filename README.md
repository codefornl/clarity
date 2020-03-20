# clarity
Clarity Case Studies (https://clarity.codefor.nl) is a project to create and share curated sets (cbases) of use cases of civic tech and gov tech. It runs on top of the cbase API at https://cbase.codefor.nl, which is an open HAL complient API.

Please note that the live application is currently running branch "non_dockered_version".

## environment variables
BASE_URI : url of the cbase api

## Run a development environment

The development environment will connect to the cbase.codefor.nl BASE_URI by default. Override it if you have a local setup for the API too.

```
docker build -t clarity_dev_local:0.0.1 .
docker run -v $(pwd)/public:/var/www/html/public -v $(pwd)/private:/var/www/html/private -p 8080:80 clarity_dev_local:0.0.1
```

You can then access the site at http://localhost:8080 and make changes in your local php files.
