SELECT DISTINCT
  REGEXP_REPLACE(CourseTemplate.Code, '090\.([^\.]+)\.(.*)', '\2') AS "courseTemplate"
FROM
  d2l_organizational_unit_ancestor OrgUnitAncestor,
  d2l_organizational_unit CourseTemplate
WHERE
  OrgUnitAncestor.AncestorOrgUnitType = 'College' AND
  OrgUnitAncestor.AncestorOrgUnitCode = 'COL.090.CORE' AND
  OrgUnitAncestor.OrgUnitType = 'Course Template' AND
  CourseTemplate.OrgUnitId = OrgUnitAncestor.OrgUnitId AND
  REGEXP_LIKE(CourseTemplate.Code, '090\.([^\.]+)\.(.*)')
union select n'AAS2140' as "courseTemplate" from dual
union select n'ANTH2045' as "courseTemplate" from dual
union select n'ENGL2140' as "courseTemplate" from dual
union select n'PERS2004' as "courseTemplate" from dual
union select n'TURK1002' as "courseTemplate" from dual
