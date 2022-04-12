<?xml version='1.0' encoding="ISO-8859-1" standalone="no" ?>
<WCS_Capabilities
   version="1.0.0"
   updateSequence="0"
   xmlns="http://www.opengis.net/wcs"
   xmlns:xlink="http://www.w3.org/1999/xlink"
   xmlns:gml="http://www.opengis.net/gml"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www.opengis.net/wcs http://schemas.opengis.net/wcs/1.0.0/wcsCapabilities.xsd">

<Service>
  <Name>OGC:WCS</Name>
  <Title>Project <!--{$project->id}--> :: <!--{$title|escape:'htmlall'}--></Title>
</Service>

<Capability>
  <Request>
  <GetCapabilities>
    <DCPType><HTTP><Get><OnlineResource xlink:type="simple" xlink:href="<!--{$onlineresource|escape:'htmlall'}-->"/></Get></HTTP></DCPType>
  </GetCapabilities>
  <GetCoverage>
    <DCPType><HTTP><Get><OnlineResource xlink:type="simple" xlink:href="<!--{$onlineresource|escape:'htmlall'}-->"/></Get></HTTP></DCPType>
  </GetCoverage>
  </Request>
  <Exception>
    <Format>application/vnd.ogc.se_xml</Format>
  </Exception>
</Capability>

<ContentMetadata>
<!--{section name=i loop=$projectlayers}-->
<!--{assign var=projectlayer value=$projectlayers[i]}-->
<!--{assign var=layer value=$projectlayer->layer}-->
<CoverageOfferingBrief>
  <name><!--{$layer->id}--></name>
  <label><!--{$layer->name|escape:'htmlall'}--></label>
  <lonLatEnvelope srsName="urn:ogc:def:crs:OGC:1.3:CRS84">
  <gml:pos><!--{$bboxlx}--> <!--{$bboxly}--></gml:pos>
  <gml:pos><!--{$bboxux}--> <!--{$bboxuy}--></gml:pos>
  </lonLatEnvelope>
</CoverageOfferingBrief>
<!--{/section}-->
</ContentMetadata>

</WCS_Capabilities>
