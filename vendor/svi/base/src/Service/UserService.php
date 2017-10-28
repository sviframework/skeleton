<?php

namespace Svi\BaseBundle\Service;

use Svi\BaseBundle\BundleTrait;
use Svi\BaseBundle\ContainerAware;

abstract class UserService extends ContainerAware
{
    use BundleTrait;

	public function logout()
	{
		$this->c->getSessionService()->uns('uid');
		$this->c->getCookiesService()->remove('REMEMBERME');
	}

	protected function loginUser($id, $remember = false)
	{
		$this->c->getSessionService()->set('uid', $id);
		if ($remember) {
			$this->remember($id);
		}
	}

	protected function getAuthorisedUserId()
	{
		if ($id = $this->c->getSessionService()->get('uid')) {
			return $id;
		} elseif ($id = $this->getRememberId()) {
			$this->loginUser($id);
			return $id;
		}

		return null;
	}

	protected function getRememberId()
	{
		if ($this->c->getCookiesService()->has('REMEMBERME')) {
			$cookie = openssl_decrypt(
                base64_decode($this->c->getCookiesService()->get('REMEMBERME')),
			    'BF-CBC',
                $this->getParameter('secret'), 0, 'fL34SpFw'
            );
			$data = explode('[|]', $cookie);
			if (array_key_exists(1, $data)) {
				return $data[1];
			}
		}

		return null;
	}

	protected function remember($id)
	{
		$login = openssl_encrypt(
            time() . '[|]' . $id . '[|]' . microtime(),
            'BF-CBC',
			$this->getParameter('secret'), 0, 'fL34SpFw');
		$this->c->getCookiesService()->set('REMEMBERME', base64_encode($login), 60*60*24*365);
	}

} 