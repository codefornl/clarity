# clarity
Clarity Case Studies

## environment variables
BASE_URI : url of the cbase api

## Run a development environment

The development environment will connect to the cbase.codefor.nl BASE_URI by default. Override it if you have a local setup for the API too.

`docker build .`
`docker run -v <localdir>/public:/var/www/html/public -v <localdir>/private:/var/www/html/private -p 8080:80 <your built image>`

You can then access the site at http://localhost:8080 and make changes in your local php files.