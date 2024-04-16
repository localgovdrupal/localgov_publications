# LocalGov Publications

This module provides publication content types and configuration for the LocalGov Drupal distribution.

The intention is to support councils to publish publications in accessible HTML rather than in PDF files.

We use Drupal's book module to provide navigation between hierarchically related pages of a publication.

It is also possible to create a single page publication with inline navigation between headings. 

## Content types

The content types this module provides are:
- Publication cover page
- Publication page

Publication pages are what make up the publication. They can be arranged in a 
hierarchy, which will be used to build the navigation inside the publication.
Use of the hierarchy is optional. Single page publications can be created.

Publication cover pages are intended to act as a link to publications.
They can reference multiple publications (EG for multiple versions of the same 
content) and allow documents (Like a PDF) to be uploaded to the cover page for 
people who don't want to read online. Use of publication cover pages is optional.

## Other features

The module includes two types of navigation, both of which are configured to 
appear on publication pages when appropriate.

Publication navigation appears when a publication has multiple pages, with links
to those pages.

In-page navigation appears when a publication page uses h2 headings, and 
provides jump links inside the page that link to those headings.

## Installing

You can install this module with the following composer command.

```
composer require localgovdrupal/localgov_publications:^1.0.0
```

## Issues

If you run into issues using this module, please report them at https://github.com/localgovdrupal/localgov_publications/issues

## Maintainers 

This project is currently maintained by:
 - Finn Lewis https://github.com/finnlewis
 - Justine Pocock https://github.com/justinepocock
 - Rupert Jabelman https://github.com/rupertj
