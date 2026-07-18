public function viewSystemDashboard(User $user): bool
{
    return $this->hasRole(
        $user,
        'super_admin',
        'calo_administrator',
        'monitoring_evaluation_team'
    );
}

public function viewCollegeDashboard(
    User $user,
    int $collegeId
): bool {
    return $this->isAdministrator($user)
        || (
            $this->sameCollege($user, $collegeId)
            && $this->hasRole(
                $user,
                'college_admin',
                'faculty_extension_coordinator',
                'college_department_head'
            )
        );
}
