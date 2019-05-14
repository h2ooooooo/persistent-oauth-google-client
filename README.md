# Persistent OAuth Google Client

A persistent OAuth Google Client integration to solve all the OAuth stuff when using server2server communication.

## Setup

1. Sign into the [Google API console developer panel](https://console.developers.google.com)
2. Create a new project called `SimpleGoogleClient`, or whatever you want
3. Click "Library" in the menu and find the libraries you want the client to support
4. Go to "Credentials" in the menu and access the "OAuth consent screen" tab
5. Set up the system as follows:
    1. **Application type**: Public
    2. **Application name**: SimpleGoogleClient
    3. **Support email**: _(your email)_
    4. Add the scope for whatever API you need (this depends on the services you want)
    5. **Authorized domains**: _(your domain)_
    6. **Application Homepage link**: _(a link to your website frontpage)_
    7. **Application Privacy Policy link**: _(a link to your website privacy policy)_
    8. **Application Terms link**: _(a link to your website terms)_ 
6. Save the credentials and go back to the "Credentials" tab and click "Create credentials"
7. Select "Oauth client ID - Requests user consent so your app.." from the create menu
8. Select "Other" in the Application type dropdown and name it whatever you want
9. Click "OK" in the popup that comes up and click the little download icon next to your OAuth credential row
10. Finally take the downloaded JSON file and put it anywhere on your server, and make sure you reference it using `$config->setAuthConfig($pathToAuthConfigJson)`

## Examples

_**NOTE**: These examples need you to create an oauth credentials file first!_

### Basic CLI

Examples using basic CLI can be found under the `examples/cli` directory.

| Link                              | Service | Description                                        |
|-----------------------------------|---------|----------------------------------------------------|
| [🔗](./examples/cli/gmail.php)    | Gmail   | Iterates through all the messages in a gmail inbox |

### CLImax

Examples using CLImax can be found under the `examples/climax` directory.


| Link                                 | Service | Description                                        |
|--------------------------------------|---------|----------------------------------------------------|
| [🔗](./examples/climax/gmail.php)    | Gmail   | Iterates through all the messages in a gmail inbox |