SELECT DISTINCT
  CourseSection.SisTermCode AS "termCode",
  CourseSection.SisCrn AS "crn"
FROM
  d2l_organizational_unit_ancestor OrgUnitAncestor,
  d2l_organizational_unit CourseSection
WHERE
  OrgUnitAncestor.AncestorOrgUnitType = 'College' AND
  OrgUnitAncestor.AncestorOrgUnitCode = 'COL.090.CORE' AND
  OrgUnitAncestor.OrgUnitType = 'Section' AND
  CourseSection.OrgUnitId = OrgUnitAncestor.OrgUnitId AND
  CourseSection.SisTermCode = :termCode AND
  CourseSection.SisCrn IS NOT NULL
