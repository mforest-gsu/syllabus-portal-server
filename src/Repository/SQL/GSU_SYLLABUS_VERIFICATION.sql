select distinct
  CourseOffering.SisTermCode as "termCode",
  CourseOffering.SisCrn as "crn"
from
  d2l_organizational_unit_ancestor OrgUnitAncestor,
  d2l_organizational_unit CourseOffering
where
  OrgUnitAncestor.AncestorOrgUnitType = 'College' and
  OrgUnitAncestor.AncestorOrgUnitCode = 'COL.090.CORE' and
  OrgUnitAncestor.OrgUnitType = 'Section' AND
  OrgUnitAncestor.OrgUnitCode like '%ECON1101%' AND
  CourseOffering.OrgUnitId = OrgUnitAncestor.OrgUnitId AND
  CourseOffering.SisTermCode = :TermCode AND
  CourseOffering.SisCrn is not null
