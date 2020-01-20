<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn="urn:openhie.org:openinfoman-hwr:stored-function:health_worker_update_provider_facility">
	<!-- DOES NOT REALLY DO ANYTHING AS THERE IS NO DATA TO UDPATE -->
	<requestParams>                     
	  <id><xsl:attribute name='entityID'><xsl:value-of select="substring-before(/form/@id,'/')"/></xsl:attribute></id>                     
	  <facility><xsl:attribute name='entityID'><xsl:value-of select="substring-after(/form/@id,'/')"/></xsl:attribute></facility>                                        
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
