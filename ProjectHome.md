# Origo - social client #
## Description ##

Today personal profiles within one social networking platform are heavily linked between each other but are not connected to other networks. Semantic technologies such as the Friend-Of-A-Friend ([FOAF](http://xmlns.com/foaf/0.1/)) ontology can break those data silos. This can be achieved by, first, describing persons in a semantic, machine understandable manner and second by linking their profiles with foaf:knows to express relationships between them. Furthermore the [RELATIONSHIP](http://vocab.org/relationship/) ontology is built on top of FOAF and is able to describe different types of relationships two person profiles can have between each other. However, since FOAF profiles are usually distributed, we need a standard way of accessing them. The preferred way of fulfilling those requirements is called Linked Data and makes use of dereferenceable URIs applied to foaf:Person entities. In order to merge external profiles of one and the same person to one FOAF profile with a single personal URI we utilize the owl:sameAs property. RDF data can often be gained by building wrappers that translate custom data from social networks to RDF because they normally provide programmable interfaces (e.g. RESTful APIs). Origo is an approach which implements those ideas in terms of a server - Web-client application.

## Online Demo ##

Visit http://www.origo-client.com/demo/client for a full featured online demo.

The personal URI for this demo person is: http://www.origo-client.com/demo/profile#me

  * Username: demo
  * Password: demo

There's a database reset every 15 minutes.

## Screenshots ##

![![](http://www.origo-client.com/screenshots/screenshot_login_thumb.png)](http://www.origo-client.com/screenshots/screenshot_login.png)
![![](http://www.origo-client.com/screenshots/screenshot_dashboard_thumb.png)](http://www.origo-client.com/screenshots/screenshot_dashboard.png)
![![](http://www.origo-client.com/screenshots/screenshot_editor_personal_thumb.png)](http://www.origo-client.com/screenshots/screenshot_editor_personal.png)
![![](http://www.origo-client.com/screenshots/screenshot_editor_relationships_thumb.png)](http://www.origo-client.com/screenshots/screenshot_editor_relationships.png)
![![](http://www.origo-client.com/screenshots/screenshot_browser_thumb.png)](http://www.origo-client.com/screenshots/screenshot_browser.png)

## Publications ##
  * M. Volke, T. Liebig,<br><a href='http://www.uni-ulm.de/fileadmin/website_uni_ulm/iui.inst.090/Publikationen/2009/Origo-ESWC09.pdf'>Origo - A Client for a Distributed Semantic Social Network</a>.<br>Poster at the European Semantic Web Conference (<a href='http://www.eswc2009.org/'>ESWC 2009</a>), Heraklion, Greece, 2009.</li></ul>

## Documentation (german only) ##

There's also a documentation available in german language:
  * HTML: http://www.origo-client.com/docs/xhtml
  * PDF: http://www.origo-client.com/docs/pdf/index.pdf