<?php
/**
 * Class FranceConnectToken
 *
 * User: tveron
 * Date: 08/12/2016
 * Time: 15:01
 *
 * @package   KleeGroup\FranceConnectBundle\Security\Core\Authentication\Token
 * @author    tveron
 * @copyright 2016 Klee Group
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @link      https://github.com/KleeGroup/FranceConnect-Symfony
 */

namespace KleeGroup\FranceConnectBundle\Security\Core\Authentication\Token;


use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class FranceConnectToken extends AbstractToken
{
    /**
     * @var string
     */
    private $fcIdentity;

    /**
     * @param array $identity
     * @param array $roles
     */
    public function __construct(array $identity, array $roles = [])
    {
        parent::__construct($roles);
        $this->setAuthenticated(count($this->getRoleNames()) > 0);
        $this->fcIdentity = $identity;
        $this->setUser('anon.');
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->fcIdentity;
    }

    /**
     * @return void
     */
    public function getCredentials(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->fcIdentity, $parentData] = $data;
        $parentData = \is_array($parentData) ? $parentData : unserialize($parentData);
        parent::__unserialize($parentData);
    }

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [$this->fcIdentity, parent::__serialize()];
    }
}
