select
  TermCode AS "termCode",
  CRN AS "crn"
from
  GSU_SYLLABUS_VERIFICATION
where
  TermCode = :termCode
