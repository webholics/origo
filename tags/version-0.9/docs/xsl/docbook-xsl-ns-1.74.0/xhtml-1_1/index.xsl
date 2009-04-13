<?xml version="1.0" encoding="ASCII"?>
<!--This file was created automatically by html2xhtml-->
<!--from the HTML stylesheets.-->
<xsl:stylesheet exclude-result-prefixes="d"
                 xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:d="http://docbook.org/ns/docbook"
xmlns="http://www.w3.org/1999/xhtml" version="1.0">

<!-- ********************************************************************
     $Id: index.xsl 6910 2007-06-28 23:23:30Z xmldoc $
     ********************************************************************

     This file is part of the XSL DocBook Stylesheet distribution.
     See ../README or http://docbook.sf.net/release/xsl/current/ for
     copyright and other information.

     ******************************************************************** -->

<!-- ==================================================================== -->

<xsl:template match="d:index">
  <!-- some implementations use completely empty index tags to indicate -->
  <!-- where an automatically generated index should be inserted. so -->
  <!-- if the index is completely empty, skip it. Unless generate.index -->
  <!-- is non-zero, in which case, this is where the automatically -->
  <!-- generated index should go. -->

  <xsl:call-template name="id.warning"/>

  <xsl:if test="count(*)&gt;0 or $generate.index != '0'">
    <div>
      <xsl:apply-templates select="." mode="class.attribute"/>
      <xsl:if test="$generate.id.attributes != 0">
        <xsl:attribute name="id">
          <xsl:call-template name="object.id"/>
        </xsl:attribute>
      </xsl:if>

      <xsl:call-template name="index.titlepage"/>
      <xsl:choose>
	<xsl:when test="d:indexdiv">
	  <xsl:apply-templates/>
	</xsl:when>
	<xsl:otherwise>
	  <xsl:apply-templates select="*[not(self::d:indexentry)]"/>
	  <!-- Because it's actually valid for Index to have neither any -->
	  <!-- Indexdivs nor any Indexentries, we need to check and make -->
	  <!-- sure that at least one Indexentry exists, and generate a -->
	  <!-- wrapper dl if there is at least one; otherwise, do nothing. -->
	  <xsl:if test="d:indexentry">
	    <!-- The indexentry template assumes a parent dl wrapper has -->
	    <!-- been generated; for Indexes that have Indexdivs, the dl -->
	    <!-- wrapper is generated by the indexdiv template; however, -->
	    <!-- for Indexes that lack Indexdivs, if we don't generate a -->
	    <!-- dl here, HTML output will not be valid. -->
	    <dl>
	      <xsl:apply-templates select="d:indexentry"/>
	    </dl>
	  </xsl:if>
	</xsl:otherwise>
      </xsl:choose>

      <xsl:if test="count(d:indexentry) = 0 and count(d:indexdiv) = 0">
        <xsl:call-template name="generate-index">
          <xsl:with-param name="scope" select="(ancestor::d:book|/)[last()]"/>
        </xsl:call-template>
      </xsl:if>

      <xsl:if test="not(parent::d:article)">
        <xsl:call-template name="process.footnotes"/>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template match="d:setindex">
  <!-- some implementations use completely empty index tags to indicate -->
  <!-- where an automatically generated index should be inserted. so -->
  <!-- if the index is completely empty, skip it. Unless generate.index -->
  <!-- is non-zero, in which case, this is where the automatically -->
  <!-- generated index should go. -->

  <xsl:call-template name="id.warning"/>

  <xsl:if test="count(*)&gt;0 or $generate.index != '0'">
    <div>
      <xsl:apply-templates select="." mode="class.attribute"/>
      <xsl:if test="$generate.id.attributes != 0">
        <xsl:attribute name="id">
          <xsl:call-template name="object.id"/>
        </xsl:attribute>
      </xsl:if>

      <xsl:call-template name="setindex.titlepage"/>
      <xsl:apply-templates/>

      <xsl:if test="count(d:indexentry) = 0 and count(d:indexdiv) = 0">
        <xsl:call-template name="generate-index">
          <xsl:with-param name="scope" select="/"/>
        </xsl:call-template>
      </xsl:if>

      <xsl:if test="not(parent::d:article)">
        <xsl:call-template name="process.footnotes"/>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template match="d:index/d:indexinfo"/>
<xsl:template match="d:index/d:info"/>
<xsl:template match="d:index/d:title"/>
<xsl:template match="d:index/d:subtitle"/>
<xsl:template match="d:index/d:titleabbrev"/>

<!-- ==================================================================== -->

<xsl:template match="d:indexdiv">
  <xsl:call-template name="id.warning"/>

  <div>
    <xsl:apply-templates select="." mode="class.attribute"/>
    <xsl:if test="$generate.id.attributes != 0">
      <xsl:attribute name="id">
        <xsl:call-template name="object.id"/>
      </xsl:attribute>
    </xsl:if>

    <xsl:call-template name="anchor"/>
    <xsl:apply-templates select="*[not(self::d:indexentry)]"/>
    <dl>
      <xsl:apply-templates select="d:indexentry"/>
    </dl>
  </div>
</xsl:template>

<xsl:template match="d:indexdiv/d:title">
  <h3>
    <xsl:apply-templates select="." mode="class.attribute"/>
    <xsl:apply-templates/>
  </h3>
</xsl:template>

<!-- ==================================================================== -->

<xsl:template match="d:indexterm">
  <!-- this one must have a name, even if it doesn't have an ID -->
  <xsl:variable name="id">
    <xsl:call-template name="object.id"/>
  </xsl:variable>

  <a id="{$id}" class="indexterm"/>
</xsl:template>

<xsl:template match="d:primary|d:secondary|d:tertiary|d:see|d:seealso">
</xsl:template>

<!-- ==================================================================== -->

<xsl:template match="d:indexentry">
  <xsl:apply-templates select="d:primaryie"/>
</xsl:template>

<xsl:template match="d:primaryie">
  <dt>
    <xsl:apply-templates/>
  </dt>
  <xsl:choose>
    <xsl:when test="following-sibling::d:secondaryie">
      <dd>
        <dl>
          <xsl:apply-templates select="following-sibling::d:secondaryie"/>
        </dl>
      </dd>
    </xsl:when>
    <xsl:when test="following-sibling::d:seeie                     |following-sibling::d:seealsoie">
      <dd>
        <dl>
          <xsl:apply-templates select="following-sibling::d:seeie                                        |following-sibling::d:seealsoie"/>
        </dl>
      </dd>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template match="d:secondaryie">
  <dt>
    <xsl:apply-templates/>
  </dt>
  <xsl:choose>
    <xsl:when test="following-sibling::d:tertiaryie">
      <dd>
        <dl>
          <xsl:apply-templates select="following-sibling::d:tertiaryie"/>
        </dl>
      </dd>
    </xsl:when>
    <xsl:when test="following-sibling::d:seeie                     |following-sibling::d:seealsoie">
      <dd>
        <dl>
          <xsl:apply-templates select="following-sibling::d:seeie                                        |following-sibling::d:seealsoie"/>
        </dl>
      </dd>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template match="d:tertiaryie">
  <dt>
    <xsl:apply-templates/>
  </dt>
  <xsl:if test="following-sibling::d:seeie                 |following-sibling::d:seealsoie">
    <dd>
      <dl>
        <xsl:apply-templates select="following-sibling::d:seeie                                      |following-sibling::d:seealsoie"/>
      </dl>
    </dd>
  </xsl:if>
</xsl:template>

<xsl:template match="d:seeie|d:seealsoie">
  <dt>
    <xsl:apply-templates/>
  </dt>
</xsl:template>

</xsl:stylesheet>
