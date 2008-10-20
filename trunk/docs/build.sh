 xsltproc --xinclude -o pdf/index.fo xsl/docbook-xsl-ns-1.74.0/fo/docbook.xsl docbook/index.xml
 fop pdf/index.fo pdf/index.pdf
 xsltproc --xinclude -o xhtml/index.html xsl/xhtml.xsl docbook/index.xml
