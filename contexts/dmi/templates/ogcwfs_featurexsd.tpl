<?xml version='1.0' encoding="ISO-8859-1" ?>
<schema
   targetNamespace="http://mapserver.gis.umn.edu/mapserver"
   xmlns:ms="http://mapserver.gis.umn.edu/mapserver"
   xmlns:ogc="http://www.opengis.net/ogc"
   xmlns:xsd="http://www.w3.org/2001/XMLSchema"
   xmlns="http://www.w3.org/2001/XMLSchema"
   xmlns:gml="http://www.opengis.net/gml"
   elementFormDefault="qualified" version="0.1" >
  <import namespace="http://www.opengis.net/gml" schemaLocation="http://schemas.opengis.net/gml/2.1.2/feature.xsd" />
  <element name="<!--{$typename}-->" type="ms:<!--{$typename}-->Type" substitutionGroup="gml:_Feature" />

  <complexType name="<!--{$typename}-->Type">
    <complexContent>
      <extension base="gml:AbstractFeatureType">
        <sequence>
            <element name="msGeometry" type="gml:GeometryPropertyType" minOccurs="1" maxOccurs="1"/>
            <!--{foreach from=$attributes key=fieldname item=fieldtype}-->
            <element name="<!--{$fieldname}-->" type="<!--{$fieldtype}-->" minOccurs="1" maxOccurs="1" />
            <!--{/foreach}-->
        </sequence>
      </extension>
    </complexContent>
  </complexType>

</schema>
