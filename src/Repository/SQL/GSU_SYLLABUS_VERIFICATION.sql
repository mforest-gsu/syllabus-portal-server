SELECT DISTINCT
  CourseSection.Code AS "courseTemplate"
FROM
  d2l_organizational_unit_ancestor OrgUnitAncestor,
  d2l_organizational_unit CourseSection
WHERE
  OrgUnitAncestor.AncestorOrgUnitType = 'College' AND
  OrgUnitAncestor.AncestorOrgUnitCode = 'COL.090.CORE' AND
  OrgUnitAncestor.OrgUnitType = 'Course Template' AND
  CourseSection.OrgUnitId = OrgUnitAncestor.OrgUnitId
