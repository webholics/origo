<?xml version="1.0" encoding="ASCII"?>
<!--This file was created automatically by html2xhtml-->
<!--from the HTML stylesheets.-->
<xsl:stylesheet exclude-result-prefixes="d"
                 xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:d="http://docbook.org/ns/docbook"
xmlns="http://www.w3.org/1999/xhtml" version="1.0">

<!-- ********************************************************************
     $Id: toc.xsl 6910 2007-06-28 23:23:30Z xmldoc $
     ********************************************************************

     This file is part of the XSL DocBook Stylesheet distribution.
     See ../README or http://docbook.sf.net/release/xsl/current/ for
     copyright and other information.

     ******************************************************************** -->

<!-- ==================================================================== -->

<xsl:template match="d:toc">
  <xsl:choose>
    <xsl:when test="*">
      <xsl:if test="$process.source.toc != 0">
        <!-- if the toc isn't empty, process it -->
        <xsl:element name="{$toc.list.type}" namespace="http://www.w3.org/1999/xhtml">
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="$process.empty.source.toc != 0">
        <xsl:choose>
          <xsl:when test="parent::d:section                           or parent::d:sect1                           or parent::d:sect2                           or parent::d:sect3                           or parent::d:sect4                           or parent::d:sect5">
            <xsl:apply-templates select="parent::*" mode="toc.for.section"/>
          </xsl:when>
          <xsl:when test="parent::d:article">
            <xsl:apply-templates select="parent::*" mode="toc.for.component"/>
          </xsl:when>
          <xsl:when test="parent::d:book                           or parent::d:part">
            <xsl:apply-templates select="parent::*" mode="toc.for.division"/>
          </xsl:when>
          <xsl:when test="parent::d:set">
            <xsl:apply-templates select="parent::*" mode="toc.for.set"/>
          </xsl:when>
          <!-- there aren't any other contexts that allow toc -->
          <xsl:otherwise>
            <xsl:message>
              <xsl:text>I don't know how to make a TOC in this context!</xsl:text>
            </xsl:message>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="d:tocpart|d:tocchap                      |d:toclevel1|d:toclevel2|d:toclevel3|d:toclevel4|d:toclevel5">
  <xsl:variable name="sub-toc">
    <xsl:if test="d:tocchap|d:toclevel1|d:toclevel2|d:toclevel3|d:toclevel4|d:toclevel5">
      <xsl:choose>
        <xsl:when test="$toc.list.type = 'dl'">
          <dd>
            <xsl:element name="{$toc.list.type}" namespace="http://www.w3.org/1999/xhtml">
              <xsl:apply-templates select="d:tocchap|d:toclevel1|d:toclevel2|d:toclevel3|d:toclevel4|d:toclevel5"/>
            </xsl:element>
          </dd>
        </xsl:when>
        <xsl:otherwise>
          <xsl:element name="{$toc.list.type}" namespace="http://www.w3.org/1999/xhtml">
            <xsl:apply-templates select="d:tocchap|d:toclevel1|d:toclevel2|d:toclevel3|d:toclevel4|d:toclevel5"/>
          </xsl:element>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:if>
  </xsl:variable>

  <xsl:apply-templates select="d:tocentry[position() != last()]"/>

  <xsl:choose>
    <xsl:when test="$toc.list.type = 'dl'">
      <dt>
        <xsl:apply-templates select="d:tocentry[position() = last()]"/>
      </dt>
      <xsl:copy-of select="$sub-toc"/>
    </xsl:when>
    <xsl:otherwise>
      <li>
        <xsl:apply-templates select="d:tocentry[position() = last()]"/>
        <xsl:copy-of select="$sub-toc"/>
      </li>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="d:tocentry|d:tocfront|d:tocback">
  <xsl:choose>
    <xsl:when test="$toc.list.type = 'dl'">
      <dt>
        <xsl:call-template name="tocentry-content"/>
      </dt>
    </xsl:when>
    <xsl:otherwise>
      <li>
        <xsl:call-template name="tocentry-content"/>
      </li>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="d:tocentry[position() = last()]" priority="2">
  <xsl:call-template name="tocentry-content"/>
</xsl:template>

<xsl:template name="tocentry-content">
  <xsl:variable name="targets" select="key('id',@linkend)"/>
  <xsl:variable name="target" select="$targets[1]"/>

  <xsl:choose>
    <xsl:when test="@linkend">
      <xsl:call-template name="check.id.unique">
        <xsl:with-param name="linkend" select="@linkend"/>
      </xsl:call-template>
      <a>
        <xsl:attribute name="href">
          <xsl:call-template name="href.target">
            <xsl:with-param name="object" select="$target"/>
          </xsl:call-template>
        </xsl:attribute>
        <xsl:apply-templates/>
      </a>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- ==================================================================== -->

<xsl:template match="*" mode="toc.for.section">
  <xsl:call-template name="section.toc"/>
</xsl:template>

<xsl:template match="*" mode="toc.for.component">
  <xsl:call-template name="component.toc"/>
</xsl:template>

<xsl:template match="*" mode="toc.for.section">
  <xsl:call-template name="section.toc"/>
</xsl:template>

<xsl:template match="*" mode="toc.for.division">
  <xsl:call-template name="division.toc"/>
</xsl:template>

<xsl:template match="*" mode="toc.for.set">
  <xsl:call-template name="set.toc"/>
</xsl:template>

<!-- ==================================================================== -->

<xsl:template match="d:lot|d:lotentry">
</xsl:template>

</xsl:stylesheet>
