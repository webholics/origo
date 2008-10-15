 xsltproc --xinclude -o pdf/index.fo docbook-xsl-ns-1.74.0/fo/docbook.xsl docbook/index.xml
 fop pdf/index.fo pdf/index.pdf
 xsltproc --xinclude -o html/index.html docbook-xsl-ns-1.74.0/html/docbook.xsl docbook/index.xml
