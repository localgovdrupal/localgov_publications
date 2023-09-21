# LocalGov Publications Alpha

This module provides publication content types and configuration for the LocalGov Drupal distribution.

The intention is to support councils to publish publications in accessible html formats rather than in PDF files.

We use Drupal's book module to provide navigation between hierarchically related pages of a publication.

It is also possible to create a single page publication with inline navigation between headings. 

## Alpha release

Please note that this is an alpha release, intented for testing. 

It is not recommended to deploy to production while in alpha, as we changes to data structure and initial configuration are likely. 

## Installing

Once and alpha release is tagged, you should be able to install with the following composer command.

```
composer require localgovdrupal/localgov_publications:^1.0.0-alpha1
```

## Testing

Please do install and test with real publication content and report any issues to Github at https://github.com/localgovdrupal/localgov_publications/issues

## Testing on Gitpod

You should be able to spin up a Gitpod install of LocalGov Drupal by:

1. [Create an account on gitpod.io](https://gitpod.io/login), if you haven't already.
2. Follow this link to [![Open in Gitpod](https://gitpod.io/button/open-in-gitpod.svg)](https://gitpod.io/#https://github.com/localgovdrupal/localgov_project) 

Once Gitpod is fired up, you should have a command line in VSCode where you can execute commands to download and enable Localgov Pulications.

```
ddev composer require localgovdrupal/localgov_publications:^1.0.0-alpha1
ddev drush en localgov_publications
```
Then you might want to use drush to generate a one time login link with

```
ddev drush uli
```

Control click the link to open in a new tab and you can start to test creating publication content. 

## Maintainers 

This project is currently maintained by: 

 - Finn Lewis https://github.com/finnlewis
 - Justine Pocock https://github.com/justinepocock
 - Rupert Jabelman https://github.com/rupertj
 - You!? let us know :)
