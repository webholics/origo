<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:d="http://docbook.org/ns/docbook"
xmlns:w="http://schemas.microsoft.com/office/word/2003/wordml"
  xmlns:v="urn:schemas-microsoft-com:vml"
  xmlns:w10="urn:schemas-microsoft-com:office:word"
  xmlns:sl="http://schemas.microsoft.com/schemaLibrary/2003/core"
  xmlns:aml="http://schemas.microsoft.com/aml/2001/core"
  xmlns:wx="http://schemas.microsoft.com/office/word/2003/auxHint"
  xmlns:o="urn:schemas-microsoft-com:office:office"
  xmlns:dt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882"
  xmlns:dbk='http://docbook.org/ns/docbook'
  xmlns:rnd='http://docbook.org/ns/docbook/roundtrip'
  xmlns:xlink='http://www.w3.org/1999/xlink'
  xmlns:exsl='http://exslt.org/common'
  exclude-result-prefixes='w v w10 sl aml wx o dt'
  extension-element-prefixes='exsl'>

  <xsl:import href='normalise-common.xsl'/>

  <xsl:output method='xml' indent="yes"/>

  <!-- ********************************************************************
       $Id: wordml2normalise.xsl 7978 2008-04-03 06:58:34Z balls $
       ********************************************************************

       This file is part of the XSL DocBook Stylesheet distribution.
       See ../README or http://nwalsh.com/docbook/xsl/ for copyright
       and other information.

       ******************************************************************** -->

  <xsl:strip-space elements='*'/>
  <xsl:preserve-space elements='w:t'/>

  <xsl:key name='style'
    match='w:style'
    use='@w:styleId'/>

  <xsl:template match="w:wordDocument">
    <dbk:article>
      <xsl:apply-templates select='w:body'/>
    </dbk:article>
  </xsl:template>

  <xsl:template match='wx:borders |
                       wx:margin-left'/>

  <xsl:template match='w:p'>
    <xsl:variable name='style'>
      <xsl:call-template name='rnd:map-paragraph-style'>
        <xsl:with-param name='style' select='w:pPr/w:pStyle/@w:val'/>
      </xsl:call-template>
    </xsl:variable>
    <dbk:para>
      <xsl:attribute name='rnd:style'>
        <xsl:value-of select='$style'/>
      </xsl:attribute>
      <xsl:if test='w:pPr/w:pStyle/@w:val and
                    $style != w:pPr/w:pStyle/@w:val'>
        <xsl:attribute name='rnd:original-style'>
          <xsl:value-of select='w:pPr/w:pStyle/@w:val'/>
        </xsl:attribute>
      </xsl:if>

      <xsl:if test='w:r[1][w:rPr/w:rStyle/@w:val = "d:attributes"] and
                    w:r[2][w:rPr/w:rStyle/@w:val = "CommentReference"]'>
        <xsl:apply-templates select='w:r[2]//w:r[w:rPr/w:rStyle/@w:val = "attribute-name"]'
          mode='rnd:attributes'/>
      </xsl:if>

      <xsl:apply-templates/>
    </dbk:para>
  </xsl:template>

  <xsl:template match='*' mode='rnd:attributes'>
    <xsl:attribute name='{w:t}'>
      <xsl:apply-templates select='following-sibling::w:r[w:rPr/w:rStyle/@w:val = "attribute-value"][1]'
        mode='rnd:attribute-value'/>
    </xsl:attribute>
  </xsl:template>

  <xsl:template match='w:r'>
    <xsl:param name='do-vert-align' select='true()'/>

    <xsl:variable name='role'>
      <xsl:choose>
        <xsl:when test='w:rPr/w:b and
                        w:rPr/w:i'>
          <xsl:text>bold-italic</xsl:text>
        </xsl:when>
        <xsl:when test='w:rPr/w:b'>
          <xsl:text>bold</xsl:text>
        </xsl:when>
        <xsl:when test='w:rPr/w:i'>
          <xsl:text>italic</xsl:text>
        </xsl:when>
        <xsl:when test='w:rPr/w:u'>
          <xsl:text>underline</xsl:text>
        </xsl:when>
        <!-- TODO: add support for other styles -->
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name='style'>
      <xsl:if test='w:rPr/w:rStyle'>
        <xsl:value-of select='w:rPr/w:rStyle/@w:val'/>
      </xsl:if>
    </xsl:variable>

    <xsl:choose>
      <xsl:when test='w:rPr/w:rStyle/@w:val = "d:attributes"'/>
      <xsl:when test='w:rPr/w:rStyle/@w:val = "CommentReference"'/>
      <xsl:when test='w:pict'>
        <xsl:variable name='filename'>
          <xsl:choose>
            <xsl:when test='contains(w:pict/w:binData/@w:name, "wordml://")'>
              <xsl:value-of select='substring-after(w:pict/w:binData/@w:name, "wordml://")'/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>image</xsl:text>
              <xsl:value-of select='count(preceding::w:pict) + 1'/>
              <xsl:text>.jpg</xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:variable>

        <xsl:call-template name='rnd:handle-image-data'>
          <xsl:with-param name='filename' select='$filename'/>
          <xsl:with-param name='data' select='w:pict/w:binData'/>
        </xsl:call-template>

        <dbk:inlinemediaobject>
          <dbk:imageobject>
            <dbk:imagedata fileref='{$filename}'>
              <xsl:if test='w:pict/v:shape/@style'>
                <xsl:attribute name='width'>
                  <xsl:value-of select='normalize-space(substring-before(substring-after(w:pict/v:shape/@style, "width:"), ";"))'/>
                </xsl:attribute>
                <xsl:attribute name='depth'>
                  <xsl:value-of select='normalize-space(substring-after(w:pict/v:shape/@style, "height:"))'/>
                </xsl:attribute>
              </xsl:if>
            </dbk:imagedata>
          </dbk:imageobject>
        </dbk:inlinemediaobject>
      </xsl:when>
      <xsl:when test='$do-vert-align and
                      w:rPr/w:vertAlign/@w:val = "d:subscript"'>
        <dbk:subscript>
          <xsl:apply-templates select='.'>
            <xsl:with-param name='do-vert-align' select='false()'/>
          </xsl:apply-templates>
        </dbk:subscript>
      </xsl:when>
      <xsl:when test='$do-vert-align and
                      w:rPr/w:vertAlign/@w:val = "d:superscript"'>
        <dbk:superscript>
          <xsl:apply-templates select='.'>
            <xsl:with-param name='do-vert-align' select='false()'/>
          </xsl:apply-templates>
        </dbk:superscript>
      </xsl:when>
      <xsl:when test='w:endnoteRef and
                      parent::w:p/parent::w:endnote and
                      count(w:rPr|w:endnoteRef) = count(*)'/>
      <xsl:when test='w:footnoteRef'/> <!-- is a label supplied? -->
      <xsl:when test='w:footnote|w:endnote'>
        <dbk:footnote>
          <xsl:apply-templates select='w:footnote|w:endnote'/>
        </dbk:footnote>
      </xsl:when>
      <xsl:when test='$role != "" or $style != ""'>
        <dbk:emphasis>
          <xsl:if test='$role != ""'>
            <xsl:attribute name='role'>
              <xsl:value-of select='$role'/>
            </xsl:attribute>
          </xsl:if>
          <xsl:if test='$style != ""'>
            <xsl:attribute name='rnd:style'>
              <xsl:call-template name='rnd:map-character-style'>
                <xsl:with-param name='style' select='$style'/>
              </xsl:call-template>
            </xsl:attribute>
          </xsl:if>
          <xsl:apply-templates/>
        </dbk:emphasis>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <!-- An application may wish to override this template -->
  <xsl:template name='rnd:handle-image-data'>
    <xsl:param name='filename'/>
    <xsl:param name='data'/>

    <xsl:if test='element-available("exsl:document")'>
      <exsl:document href='{$filename}.b64' method='text'>
        <xsl:value-of select='w:pict/w:binData'/>
      </exsl:document>
    </xsl:if>
  </xsl:template>

  <xsl:template match='w:hlink'>
    <dbk:link xlink:href='{@w:dest}'>
      <xsl:apply-templates/>
    </dbk:link>
  </xsl:template>

  <!-- Soft returns don't really have an equivalent in DocBook,
     - except in literal line environments.
    -->
  <xsl:template match='w:br'>
    <xsl:text>&#xa;</xsl:text>
  </xsl:template>

  <xsl:template match='w:tbl'>
    <xsl:variable name='tbl.style'
      select='key("d:style", w:tblPr/w:tblStyle/@w:val) | .'/>

    <xsl:variable name='border.top'>
      <xsl:choose>
        <xsl:when test='$tbl.style/w:tblPr/w:tblBorders/w:top[not(@w:val = "d:nil" or @w:val = "d:none")]'>1</xsl:when>
        <xsl:when test='$tbl.style/w:tblPr/w:tblBorders/w:top[@w:val = "d:nil" or @w:val = "d:none"]'>0</xsl:when>
        <xsl:when test='w:tr[1]/w:tc[w:tcPr/w:tcBorders/w:top[not(@w:val = "d:nil" or @w:val = "d:none")]]'>1</xsl:when>
        <xsl:otherwise>0</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name='border.bottom'>
      <xsl:choose>
        <xsl:when test='$tbl.style/w:tblPr/w:tblBorders/w:bottom[not(@w:val = "d:nil" or @w:val = "d:none")]'>1</xsl:when>
        <xsl:when test='$tbl.style/w:tblPr/w:tblBorders/w:bottom[@w:val = "d:nil" or @w:val = "d:none"]'>0</xsl:when>
        <xsl:when test='w:tr[1]/w:tc[w:tcPr/w:tcBorders/w:bottom[not(@w:val = "d:nil" or @w:val = "d:none")]]'>1</xsl:when>
        <xsl:otherwise>0</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name='border.left'>
      <xsl:choose>
        <xsl:when test='$tbl.style/w:tblPr/w:tblBorders/w:left[not(@w:val = "d:nil" or @w:val = "d:none")]'>1</xsl:when>
        <xsl:when test='$tbl.style/w:tblPr/w:tblBorders/w:left[@w:val = "d:nil" or @w:val = "d:none"]'>0</xsl:when>
        <xsl:when test='w:tr[1]/w:tc[w:tcPr/w:tcBorders/w:left[not(@w:val = "d:nil" or @w:val = "d:none")]]'>1</xsl:when>
        <xsl:otherwise>0</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name='border.right'>
      <xsl:choose>
        <xsl:when test='$tbl.style/w:tblPr/w:tblBorders/w:right[not(@w:val = "d:nil" or @w:val = "d:none")]'>1</xsl:when>
        <xsl:when test='$tbl.style/w:tblPr/w:tblBorders/w:right[@w:val = "d:nil" or @w:val = "d:none"]'>0</xsl:when>
        <xsl:when test='w:tr[1]/w:tc[w:tcPr/w:tcBorders/w:rightt[not(@w:val = "d:nil" or @w:val = "d:none")]]'>1</xsl:when>
        <xsl:otherwise>0</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>

    <dbk:informaltable>
      <xsl:choose>
        <xsl:when test='$border.top = "1" and $border.bottom = "1" and
                        $border.left = "1" and $border.right = "1"'>
          <xsl:attribute name='frame'>all</xsl:attribute>
        </xsl:when>
        <xsl:when test='$border.top = "1" and $border.bottom = "1"'>
          <xsl:attribute name='frame'>topbot</xsl:attribute>
        </xsl:when>
        <xsl:when test='$border.left = "1" and $border.right = "1"'>
          <xsl:attribute name='frame'>sides</xsl:attribute>
        </xsl:when>
        <xsl:when test='$border.top = "1"'>
          <xsl:attribute name='frame'>top</xsl:attribute>
        </xsl:when>
        <xsl:when test='$border.bottom = "1"'>
          <xsl:attribute name='frame'>bottom</xsl:attribute>
        </xsl:when>
      </xsl:choose>

      <!-- TODO: analyse column widths -->

      <dbk:tgroup>
        <xsl:apply-templates select='w:tblGrid'/>
        <dbk:tbody>
          <xsl:apply-templates select='w:tr'/>
        </dbk:tbody>
      </dbk:tgroup>
    </dbk:informaltable>
  </xsl:template>
  <xsl:template match='w:tblPr'/>
  <xsl:template match='w:tblGrid/w:gridCol'>
    <dbk:colspec colwidth='{@w:w}*'
      colname='column-{count(preceding-sibling::w:gridCol) + 1}'/>
  </xsl:template>
  <xsl:template match='w:tr'>
    <dbk:row>
      <xsl:apply-templates/>
    </dbk:row>
  </xsl:template>
  <xsl:template match='w:tc'>
    <xsl:variable name='tbl.style'
      select='ancestor::w:tbl[1] |
              key("d:style", ancestor::w:tbl[1]/w:tblPr/w:tblStyle/@w:val)'/>

    <dbk:entry>
      <xsl:if test='$tbl.style/w:tblPr/w:tblBorders/w:insideH[not(@w:val = "d:nil" or @w:val = "d:none")] |
                    w:tcPr/w:tcBorders/w:bottom[not(@w:val = "d:nil" or @w:val = "d:none")]'>
        <xsl:attribute name='rowsep'>1</xsl:attribute>
      </xsl:if>
      <xsl:if test='$tbl.style/w:tblPr/w:tblBorders/w:insideV[not(@w:val = "d:nil" or @w:val = "d:none")] |
                    w:tcPr/w:tcBorders/w:right[not(@w:val = "d:nil" or @w:val = "d:none")]'>
        <xsl:attribute name='colsep'>1</xsl:attribute>
      </xsl:if>

      <xsl:variable name='this.colnum'
        select='count(preceding-sibling::w:tc) + 1 +
                sum(preceding-sibling::w:tc/w:tcPr/w:gridSpan/@w:val) -
                count(preceding-sibling::w:tc/w:tcPr/w:gridSpan[@w:val])'/>

      <xsl:if test='w:tcPr/w:gridSpan[@w:val > 1]'>
        <xsl:attribute name='namest'>
          <xsl:text>column-</xsl:text>
          <xsl:value-of select='$this.colnum'/>
        </xsl:attribute>
        <xsl:attribute name='nameend'>
          <xsl:text>column-</xsl:text>
          <xsl:value-of select='$this.colnum + w:tcPr/w:gridSpan/@w:val - 1'/>
        </xsl:attribute>
      </xsl:if>

      <xsl:if test='w:tcPr/w:vmerge[@w:val = "d:restart"]'>
        <xsl:attribute name='morerows'>
          <xsl:call-template name='rnd:count-rowspan'>
            <xsl:with-param name='row' select='../following-sibling::w:tr[1]'/>
            <xsl:with-param name='colnum' select='$this.colnum'/>
          </xsl:call-template>
        </xsl:attribute>
      </xsl:if>

      <xsl:apply-templates/>
    </dbk:entry>
  </xsl:template>

  <xsl:template match='w:pStyle |
                       w:rStyle |
                       w:proofErr |
                       w:fldData |
                       w:instrText'/>

  <xsl:template name='rnd:count-rowspan'>
    <xsl:param name='row' select='/..'/>
    <xsl:param name='colnum' select='0'/>

    <xsl:variable name='cell'
      select='$row/w:tc[count(preceding-sibling::w:tc) + 1 +
              sum(preceding-sibling::w:tc/w:tcPr/w:gridSpan/@w:val) -
              count(preceding-sibling::w:tc/w:tcPr/w:gridSpan[@w:val]) = $colnum]'/>

    <xsl:choose>
      <xsl:when test='not($cell)'>
        <xsl:text>0</xsl:text>
      </xsl:when>
      <xsl:when test='$cell/w:tcPr/w:vmerge[not(@w:val = "d:restart")]'>
        <xsl:variable name='remainder'>
          <xsl:call-template name='rnd:count-rowspan'>
            <xsl:with-param name='row'
              select='$row/following-sibling::w:tr[1]'/>
            <xsl:with-param name='colnum' select='$colnum'/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:value-of select='$remainder + 1'/>
      </xsl:when>
      <xsl:otherwise>0</xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match='w:hdr|w:ftr'/>

</xsl:stylesheet>
