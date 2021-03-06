# Jeyser CRM - N7 Consulting

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b2a395d1-acaa-4305-a30e-3a326fdb7c3a/small.png)](https://insight.sensiolabs.com/projects/b2a395d1-acaa-4305-a30e-3a326fdb7c3a)

Jeyser-CRM (also known as Incipio) is a project aiming at the creation an open-source [CRM](https://en.wikipedia.org/wiki/Customer_relationship_management) / [ERP](https://en.wikipedia.org/wiki/Enterprise_resource_planning) for all Junior Entreprises belonging or not to [CNJE](http://www.junior-entreprises.com/) and [JADE](http://www.jadenet.org/).

This Github repository is hosting N7 Consulting, [ENSEEIHT](http://www.enseeiht.fr/fr/index.html)'s Junior-Entreprise, version of Jeyser. It is currently (17/11/2016) the only version of Incipio under active development. You can try a demo (resetted every hour) of Jeyser [Heroku](https://jeyser-demo.herokuapp.com/)

That version fixes numerous bugs, improves a lot code quality and existing modules, updates to Symfony 3 and adds several functionnalities to original Incipio version. Namely skills mapping between projects and members in HR module, data import from external sources, manage key parameters from web. A particular care is given to user interface and user experience to provide the most friendly use possible.

#Details

Jeyser can be installed and secured with only 5 bash commands. Read the [install documentation](http://jeyser-crm.n7consulting.fr/install/) to get your own Jeyser and its free SSL certificate up and running.

It is shipped with some demonstration doctypes to help you to write your own.

#RoadMap

- Functionnalities : add phase lock when an invoice has been issued. Add some data visualization.
- Translation : Move every hard coded text in views to translation files to enable i18n of Jeyser.
- Code Quality : Keep Platinium medal on Sensiolabs Insights. 
- Code coverage : introduce behavioural tests with Behat.


# Licence

[![License](https://img.shields.io/badge/Licence-GNU%20AGPL-red.svg?style=flat-square)](LICENSE)

# Contributing

Interested in contributing to Jeyser ? It would be a great pleasure to welcome you in the community. To begin with, check our [contribution guide](http://jeyser-crm.n7consulting.fr/dev/contributing/) out.

# Bugs and Security

We are using Jeyser for our business, thus we try to keep as secure as it could be. However, if you find a security issue, mail us at dsi[at]n7consulting.fr. 

If you are encountering a bug, raise an issue on Github, and we would be glad to fix it as quick as we can.
