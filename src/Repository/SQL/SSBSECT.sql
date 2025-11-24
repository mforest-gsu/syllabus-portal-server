WITH
  CourseSection AS (
    SELECT
      SSBSECT.SSBSECT_TERM_CODE AS termCode,
      SSBSECT.SSBSECT_CRN AS crn,
      SSBSECT.SSBSECT_SUBJ_CODE AS subjectCode,
      SSBSECT.SSBSECT_CRSE_NUMB AS courseNumber,
      SSBSECT.SSBSECT_SEQ_NUMB AS courseSequence,
      SSBSECT.SSBSECT_SCHD_CODE AS scheduleCode,
      SSBSECT.SSBSECT_CAMP_CODE AS campusCode,
      SCBCRSE.SCBCRSE_EFF_TERM AS courseEffectiveTerm,
      NVL(SSBSECT.SSBSECT_CRSE_TITLE, SCBCRSE.SCBCRSE_TITLE) AS courseTitle,
      NVL(SSBOVRR_COLL_CODE, SCBCRSE_COLL_CODE) AS collegeCode,
      NVL(SSBOVRR_DEPT_CODE, SCBCRSE_DEPT_CODE) AS departmentCode
    FROM
      SSBSECT@BIPROD_BREPT_LINK_MFOREST SSBSECT,
      SCBCRSE@BIPROD_BREPT_LINK_MFOREST SCBCRSE,
      SSBOVRR@BIPROD_BREPT_LINK_MFOREST SSBOVRR
    WHERE
      SSBSECT.SSBSECT_TERM_CODE = :termCode AND
      SSBSECT.SSBSECT_SSTS_CODE = 'A' AND
      SSBSECT.SSBSECT_INTG_CDE IS NULL AND
      SSBSECT.SSBSECT_MAX_ENRL > 0 AND
      -- SCBCRSE - Course "template"
      SCBCRSE.SCBCRSE_SUBJ_CODE = SSBSECT.SSBSECT_SUBJ_CODE AND
      SCBCRSE.SCBCRSE_CRSE_NUMB = SSBSECT.SSBSECT_CRSE_NUMB AND
      SCBCRSE.SCBCRSE_CSTA_CODE = 'A' AND
      SCBCRSE.SCBCRSE_EFF_TERM = (
        SELECT
          MAX(i.SCBCRSE_EFF_TERM)
        FROM
          SCBCRSE@BIPROD_BREPT_LINK_MFOREST i
        WHERE
          i.SCBCRSE_SUBJ_CODE = SSBSECT.SSBSECT_SUBJ_CODE AND
          i.SCBCRSE_CRSE_NUMB = SSBSECT.SSBSECT_CRSE_NUMB AND
          i.SCBCRSE_EFF_TERM <= SSBSECT.SSBSECT_TERM_CODE AND
          i.SCBCRSE_CSTA_CODE = 'A'
      ) AND
      -- SSBOVRR - Course section overrides
      SSBOVRR_TERM_CODE(+) = SSBSECT_TERM_CODE AND
      SSBOVRR_CRN(+) = SSBSECT_CRN
  ),
  Term AS (
    SELECT
      STVTERM.STVTERM_CODE AS termCode,
      STVTERM.STVTERM_DESC AS termName
    FROM
      STVTERM@BIPROD_BREPT_LINK_MFOREST STVTERM
    WHERE
      STVTERM.STVTERM_CODE = :termCode
  ),
  College AS (
    SELECT
      STVCOLL_CODE AS collegeCode,
      STVCOLL_DESC AS collegeName
    FROM
      STVCOLL@BIPROD_BREPT_LINK_MFOREST
  ),
  Department AS (
    SELECT
      STVDEPT_CODE AS departmentCode,
      STVDEPT_DESC AS departmentName
    FROM
      STVDEPT@BIPROD_BREPT_LINK_MFOREST
  ),
  Campus AS (
    SELECT
      STVCAMP_CODE AS bannerCampusCode,
      CASE
        WHEN STVCAMP_CODE IN ('PF','ON','OG') THEN 'ON'
        WHEN STVCAMP_CODE IN ('PA','PC','PE','PN','PS') THEN 'PER'
        WHEN STVCAMP_CODE IN ('A','1') THEN 'ATL'
        WHEN STVCAMP_CODE IN ('I') THEN 'FOR'
        WHEN STVCAMP_CODE IN ('X','F','PF') THEN 'OFF'
        ELSE 'OTH'
      END AS campusCode,
      CASE
        WHEN STVCAMP_CODE IN ('PF','ON','OG') THEN 'Online Campus'
        WHEN STVCAMP_CODE IN ('PA','PC','PE','PN','PS') THEN 'Perimeter Campus'
        WHEN STVCAMP_CODE IN ('A','1') THEN 'Atlanta Campus'
        WHEN STVCAMP_CODE IN ('I') THEN 'Foreign Campus'
        WHEN STVCAMP_CODE IN ('X','F','PF') THEN 'Off-campus'
        ELSE 'Other USG Institution'
      END AS campusName
    FROM
      STVCAMP@BIPROD_BREPT_LINK_MFOREST
  ),
  PrimaryInstructor AS (
    SELECT
      SIRASGN_TERM_CODE AS termCode,
      SIRASGN_CRN AS crn,
      SPRIDEN_PIDM AS instructorPidm,
      SPRIDEN_ID AS instructorId,
      SPRIDEN_FIRST_NAME AS instructorFirstName,
      SPRIDEN_LAST_NAME AS instructorLastName,
      GOREMAL_EMAIL_ADDRESS AS instructorEmail
    FROM
      SIRASGN@BIPROD_BREPT_LINK_MFOREST,
      SPRIDEN@BIPROD_BREPT_LINK_MFOREST,
      GOREMAL@BIPROD_BREPT_LINK_MFOREST
    WHERE
      SIRASGN_TERM_CODE = :termCode AND
      SIRASGN_PRIMARY_IND = 'Y' AND
      -- SPRIDEN - person record
      SPRIDEN_PIDM = SIRASGN_PIDM AND
      SPRIDEN_CHANGE_IND IS NULL AND
      -- GOREMAL - active facstaff email
      GOREMAL_PIDM(+) = SPRIDEN_PIDM AND
      GOREMAL_EMAL_CODE(+) = 'FSEM' AND
      GOREMAL_STATUS_IND(+) = 'A'
  )
SELECT
  CourseSection.crn AS "crn",
  CourseSection.subjectCode AS "subjectCode",
  CourseSection.courseNumber AS "courseNumber",
  CourseSection.courseSequence AS "courseSequence",
  CourseSection.scheduleCode AS "scheduleCode",
  CourseSection.courseEffectiveTerm AS "courseEffectiveTerm",
  CourseSection.courseTitle AS "courseTitle",
  Term.termCode AS "termCode",
  Term.termName AS "termName",
  College.collegeCode AS "collegeCode",
  College.collegeName AS "collegeName",
  Department.departmentCode AS "departmentCode",
  Department.departmentName AS "departmentName",
  Campus.campusCode AS "campusCode",
  Campus.campusName AS "campusName",
  NVL(PrimaryInstructor.instructorPidm, '0') AS "instructorPidm",
  NVL(PrimaryInstructor.instructorId, 'STAFF') AS "instructorId",
  PrimaryInstructor.instructorFirstName AS "instructorFirstName",
  PrimaryInstructor.instructorLastName AS "instructorLastName",
  PrimaryInstructor.instructorEmail AS "instructorEmail"
FROM
  CourseSection,
  Term,
  College,
  Department,
  Campus,
  PrimaryInstructor
WHERE
  CourseSection.termCode = :termCode AND
  Term.termCode = CourseSection.termCode AND
  College.collegeCode = CourseSection.collegeCode AND
  Department.departmentCode = CourseSection.departmentCode AND
  Campus.bannerCampusCode = CourseSection.campusCode AND
  PrimaryInstructor.termCode(+) = CourseSection.termCode AND
  PrimaryInstructor.crn(+) = CourseSection.crn
ORDER BY
  "termCode",
  "crn"
