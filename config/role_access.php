<?php

return [
    'user_managers' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
    ],

    'college_managers' => [
        'super_admin',
        'calo_administrator',
    ],

    'program_viewers' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
        'faculty_extension_coordinator',
    ],

    'program_lookup_users' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
        'project_proponent',
        'faculty_extension_coordinator',
    ],

    'project_viewers' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
        'coordinator',
        'project_proponent',
        'faculty_extension_coordinator',
        'college_department_head',
        'community_partner',
        'external_evaluator',
        'student_volunteer',
        'alumni_partner',
        'monitoring_evaluation_team',
    ],

    'community_managers' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
    ],

    'community_lookup_users' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
        'project_proponent',
        'faculty_extension_coordinator',
        'college_department_head',
        'community_partner',
        'monitoring_evaluation_team',
    ],

    'survey_designers' => [
        'super_admin',
        'calo_administrator',
    ],

    'survey_respondents' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
        'faculty_extension_coordinator',
        'community_partner',
        'monitoring_evaluation_team',
    ],

    'priority_need_viewers' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
        'project_proponent',
        'faculty_extension_coordinator',
        'college_department_head',
        'community_partner',
        'monitoring_evaluation_team',
    ],

    'engagement_viewers' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
        'coordinator',
        'project_proponent',
        'faculty_extension_coordinator',
        'college_department_head',
        'community_partner',
        'external_evaluator',
        'student_volunteer',
        'alumni_partner',
        'monitoring_evaluation_team',
    ],

    'engagement_encoders' => [
        'super_admin',
        'calo_administrator',
        'college_admin',
        'coordinator',
        'project_proponent',
        'faculty_extension_coordinator',
    ],

    'engagement_reviewers' => [
        'super_admin',
        'calo_administrator',
    ],

    'engagement_analytics_viewers' => [
        'super_admin',
        'calo_administrator',
        'monitoring_evaluation_team',
    ],
];
