
/**********************************************************************
 install.sql file
 Required if the module adds programs to other modules
***********************************************************************/

-- Fix #102 error language "plpgsql" does not exist
-- http://timmurphy.org/2011/08/27/create-language-if-it-doesnt-exist-in-postgresql/
--
-- Name: create_language_plpgsql(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION create_language_plpgsql()
RETURNS BOOLEAN AS $$
    CREATE LANGUAGE plpgsql;
    SELECT TRUE;
$$ LANGUAGE SQL;

SELECT CASE WHEN NOT (
    SELECT TRUE AS exists FROM pg_language
    WHERE lanname='plpgsql'
    UNION
    SELECT FALSE AS exists
    ORDER BY exists DESC
    LIMIT 1
) THEN
    create_language_plpgsql()
ELSE
    FALSE
END AS plpgsql_created;

DROP FUNCTION create_language_plpgsql();


/*******************************************************
 profile_id:
    - 0: student
    - 1: admin
    - 2: teacher
    - 3: parent
 modname: should match the Menu.php entries
 can_use: 'Y'
 can_edit: 'Y' or null (generally null for non admins)
*******************************************************/
--
-- Data for Name: profile_exceptions; Type: TABLE DATA;
--


-- First group is the Orders Preorders process
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Utilities/Teacherchange.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Utilities/Teacherchange.php'
    AND profile_id=1);

-- First group is the Orders Preorders process
INSERT INTO profile_exceptions (profile_id, modname, can_use, can_edit)
SELECT 1, 'Utilities/scheduleCloner.php', 'Y', 'Y'
WHERE NOT EXISTS (SELECT profile_id
    FROM profile_exceptions
    WHERE modname='Utilities/scheduleCloner.php'
    AND profile_id=1);


/**
 * program_config Table
 *
 * syear: school year (school may have various years in DB)
 * school_id: may exists various schools in DB
 * program: convention is plugin name, for ex.: 'student_billing_premium'
 * title: for ex.: 'STUDENT_PAYMENT_RECEIPTS_[your_program_config]'
 * value: string
 */
--
-- Data for Name: program_config; Type: TABLE DATA; Schema: public; Owner: rosariosis
--



/**
 * Add module tables
 */
