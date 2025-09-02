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
  CourseOffering.OrgUnitId = OrgUnitAncestor.OrgUnitId AND
  CourseOffering.SisTermCode = :termCode AND
  CourseOffering.SisCrn is not null and
  -- exception list for Fall 2025
  CourseSection.SisCrn not in (
     '93492'  -- CONDUCTING-GRADUATE III|APCD 8003|Graduate course, remove from list
    ,'93524'  -- T RELIGION & AGING|GERO 8700|Graduate course, remove from list
    ,'93482'  -- PROJECT LAB 1|PRLB 8000|Graduate course, remove from list
    ,'92872'  -- INDEPENDENT STUDY|WLC 6990|Graduate course, remove from list
    ,'84135'  -- DISSERTATION|EDCI 9990|Dissertation courses don't have syllabi, remove from list
    ,'89197'  -- HISTORY OF MOTION PICTURES|FLME 2700|Does not appear in gosolar/not in schedule
    ,'85041'  -- HISTORY OF MOTION PICTURES|FLME 2700|Does not appear in gosolar/not in schedule
    ,'86104'  -- INTRO TO THE THEATRE|THEA 2040|Second, hidden section, not actually being offered, remove from list
  )
