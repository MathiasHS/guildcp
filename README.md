# Guild Control Panel

## Information
This is a university project, and the project has now been turned in. There will be no more updates, so this is hosted purely for educational reasons, or in case someone else wants to build upon this.

## Limitations
Because this is a university project, there were certain limitations. We were forced to use PHP, and we were supposed to write all the code from scratch ourselves. The exception was Bootstrap. We were later allowed to use packages for API requests.

## Credits
``` 
https://github.com/sondrekje - Project member
https://github.com/andreasvannebo - Project member
https://github.com/datamus - Project member
https://github.com/walomzki - Project member

https://github.com/LogansUA/blizzard-api-php-client - Originally wanted to use this until we realized it was outdated, inspiration from here.
```
 
## Repository status
This repository was originally hosted privately elsewhere, and certain sensitive information was hardcoded. The history is therefore gone. 

## Environment variables
```
GCP_MYSQL_HOSTNAME
GCP_MYSQL_USERNAME
GCP_MYSQL_PASSWORD
GCP_MYSQL_DATABASE

# The domain used for cookies (e.g localhost)
GCP_COOKIE_DOMAIN=localhost

# Blizzard API variables
GCP_CLIENT_ID
GCP_CLIENT_SECRET
GCP_REDIRECT_URI

# Development mode (true/false)
GCP_DEV=false
```

## PHP Dependencies
PHP dependencies are managed using PHP Composer. The requrired depencies can be downloaded using
`composer install`

## PHP Standard
This project is using the PHP coding standard PSR2 with some minor adjustments. You can use phpcs (PHP CodeSniffer) to parse the files. You can find our slightly modified standard in the phpcs.xml file.

## Testing locally
docker-compose.yml environment variables are correctly configured for both production and staging. You can easily test code locally using
`docker-compose up` in the root directory of the project. Alternatively, you can use apache virtual hosts, but you will have to set the environment variables yourself. 

## Running code sniffing and unit tests
If you have the PHP dependencies installed outside of your virtualized environment, you can run the `composer check` script, which will parse all files with phpcs using our modified standard, and will then proceed to run all unit tests in the /tests directory. 
**Note**: that this is run automatically after each push to the repository. 
