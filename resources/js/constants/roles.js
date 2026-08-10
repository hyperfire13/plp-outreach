export const ROLES = Object.freeze({
    SUPER_ADMIN: "super_admin",
    CALO_ADMINISTRATOR: "calo_administrator",
    COLLEGE_ADMIN: "college_admin",
    COORDINATOR: "coordinator",
    PROJECT_PROPONENT: "project_proponent",
    FACULTY_EXTENSION_COORDINATOR: "faculty_extension_coordinator",
    COLLEGE_DEPARTMENT_HEAD: "college_department_head",
    COMMUNITY_PARTNER: "community_partner",
    EXTERNAL_EVALUATOR: "external_evaluator",
    STUDENT_VOLUNTEER: "student_volunteer",
    ALUMNI_PARTNER: "alumni_partner",
    MONITORING_EVALUATION_TEAM: "monitoring_evaluation_team",
});

export const ROLE_GROUPS = Object.freeze({
    USER_MANAGERS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
        ROLES.COLLEGE_ADMIN,
    ]),

    COLLEGE_MANAGERS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
    ]),

    PROGRAM_VIEWERS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
        ROLES.COLLEGE_ADMIN,
        ROLES.FACULTY_EXTENSION_COORDINATOR,
    ]),

    PROJECT_VIEWERS: Object.freeze(Object.values(ROLES)),

    COMMUNITY_MANAGERS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
        ROLES.COLLEGE_ADMIN,
    ]),

    SURVEY_DESIGNERS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
    ]),

    SURVEY_RESPONDENTS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
        ROLES.COLLEGE_ADMIN,
        ROLES.FACULTY_EXTENSION_COORDINATOR,
        ROLES.COMMUNITY_PARTNER,
        ROLES.MONITORING_EVALUATION_TEAM,
    ]),

    PRIORITY_NEED_VIEWERS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
        ROLES.COLLEGE_ADMIN,
        ROLES.PROJECT_PROPONENT,
        ROLES.FACULTY_EXTENSION_COORDINATOR,
        ROLES.COLLEGE_DEPARTMENT_HEAD,
        ROLES.COMMUNITY_PARTNER,
        ROLES.MONITORING_EVALUATION_TEAM,
    ]),

    ENGAGEMENT_VIEWERS: Object.freeze(Object.values(ROLES)),

    ENGAGEMENT_ENCODERS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
        ROLES.COLLEGE_ADMIN,
        ROLES.COORDINATOR,
        ROLES.PROJECT_PROPONENT,
        ROLES.FACULTY_EXTENSION_COORDINATOR,
    ]),

    ENGAGEMENT_REVIEWERS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
    ]),

    ENGAGEMENT_ANALYTICS_VIEWERS: Object.freeze([
        ROLES.SUPER_ADMIN,
        ROLES.CALO_ADMINISTRATOR,
        ROLES.MONITORING_EVALUATION_TEAM,
    ]),
});
