<?php

namespace Decision\Service;

use Application\Service\AbstractAclService;

/**
 * Member service.
 */
class Member extends AbstractAclService
{

    const MIN_SEARCH_QUERY_LENGTH = 2;

    /**
     * Obtain information about the current user.
     *
     * @return Decision\Model\Member
     */
    public function getMembershipInfo()
    {
        if (!$this->isAllowed('view')) {
            $translator = $this->getTranslator();
            throw new \User\Permissions\NotAllowedException(
                $translator->translate('You are not allowed to view membership info.')
            );
        }

        $member = $this->getMemberMapper()->findByLidnr($this->getRole()->getLidnr());

        $memberships = array();
        foreach ($member->getOrganInstallations() as $install) {
            if (null !== $install->getDischargeDate()) {
                continue;
            }
            if (!isset($memberships[$install->getOrgan()->getAbbr()])) {
                $memberships[$install->getOrgan()->getAbbr()] = array();
            }
            if ($install->getFunction() != 'Lid') {
                $memberships[$install->getOrgan()->getAbbr()][] = $install->getFunction();
            }
        }

        return array(
            'member' => $member,
            'memberships' => $memberships
        );
    }

    /**
     *
     */
    public function findMemberByLidNr($lidnr)
    {
        return $this->getMemberMapper()->findByLidnr($lidnr);
    }

    /**
     * Find a member by (part of) its name.
     *
     * @param string $query (part of) the full name of a member
     * @pre $name must be at least MIN_SEARCH_QUERY_LENGTH
     *
     * @return array|null
     */
    public function searchMembersByName($query)
    {
        if (strlen($query) < self::MIN_SEARCH_QUERY_LENGTH) {
            throw new \Zend\Code\Exception\InvalidArgumentException(
                $this->getTranslator()->translate('Name must be at least ' . self::MIN_SEARCH_QUERY_LENGTH . ' characters')
            );
        }

        if (!$this->isAllowed('search')) {
            throw new \User\Permissions\NotAllowedException(
                $this->getTranslator()->translate('Not allowed to search for members.')
            );
        }

        return $this->getMemberMapper()->searchByName($query);

    }

    /**
     * Get the member mapper.
     *
     * @return \Decision\Mapper\Member
     */
    public function getMemberMapper()
    {
        return $this->sm->get('decision_mapper_member');
    }

    /**
     * Get the default resource ID.
     *
     * @return string
     */
    protected function getDefaultResourceId()
    {
        return 'member';
    }

    /**
     * Get the Acl.
     *
     * @return Zend\Permissions\Acl\Acl
     */
    public function getAcl()
    {
        return $this->sm->get('decision_acl');
    }
}
