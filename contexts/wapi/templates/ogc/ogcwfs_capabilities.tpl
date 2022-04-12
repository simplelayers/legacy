<?xml version='1.0' encoding="ISO-8859-1" standalone="no" ?>
<WFS_Capabilities
   version="1.0.0"
   updateSequence="0"
   xmlns="http://www.opengis.net/wfs"
   xmlns:ogc="http://www.opengis.net/ogc"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www.opengis.net/wfs http://schemas.opengis.net/wfs/1.0.0/WFS-capabilities.xsd">

<Service>
  <Name>WFS</Name>
  <Title>Project <!--{$project->id}-->, <!--{$title|escape:'htmlall'}--></Title>
  <OnlineResource><!--{$onlineresource|escape:'htmlall'}--></OnlineResource>
</Service>

<Capability>
<Request>
  <GetCapabilities>
    <DCPType><HTTP><Get onlineResource="<!--{$onlineresource|escape:'htmlall'}-->"/></HTTP></DCPType>
  </GetCapabilities>
    <DescribeFeatureType>
    <SchemaDescriptionLanguage>
      <XMLSCHEMA/>
    </SchemaDescriptionLanguage>
    <DCPType><HTTP><Get onlineResource="<!--{$onlineresource|escape:'htmlall'}-->"/></HTTP></DCPType>
  </DescribeFeatureType>
  <GetFeature>
    <ResultFormat>
      <GML2/>
    </ResultFormat>
    <DCPType><HTTP><Get onlineResource="<!--{$onlineresource|escape:'htmlall'}-->"/></HTTP></DCPType>
  </GetFeature>
  </Request>
</Capability>

<FeatureTypeList>
  <Operations>
  <Query/>
  </Operations>

  <!--{section name=i loop=$projectlayers}-->
  <!--{assign var=projectlayer value=$projectlayers[i]}-->
  <!--{assign var=layer value=$projectlayer->layer}-->
  <!--{if $layer->type == LayerTypes::VECTOR or $layer->type == LayerTypes::RELATIONAL}-->
  <FeatureType>
    <Name>layer_<!--{$projectlayer->id}--></Name>
    <Title><!--{$layer->name|escape:'htmlall'}--></Title>
    <SRS>EPSG:4326</SRS>
    <LatLongBoundingBox minx="<!--{$bboxlx}-->" miny="<!--{$bboxly}-->" maxx="<!--{$bboxux}-->" maxy="<!--{$bboxuy}-->" />
  </FeatureType>
  <!--{/if}-->
  <!--{/section}-->
</FeatureTypeList>

<ogc:Filter_Capabilities>
  <ogc:Spatial_Capabilities>
    <ogc:Spatial_Operators>
      <ogc:BBOX/>
    </ogc:Spatial_Operators>
  </ogc:Spatial_Capabilities>
  <ogc:Scalar_Capabilities>
    <ogc:Logical_Operators/>
    <ogc:Comparison_Operators>
      <ogc:Simple_Comparisons/>
      <ogc:Like/>
      <ogc:Between/>
    </ogc:Comparison_Operators>
    </ogc:Scalar_Capabilities>
</ogc:Filter_Capabilities>

</WFS_Capabilities>
