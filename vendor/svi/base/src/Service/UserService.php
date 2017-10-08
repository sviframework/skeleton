<?php

namespace Svi\Base\Service;

use Svi\Base\BundleTrait;
use Svi\Base\ContainerAware;

abstract class UserService extends ContainerAware
{
    use BundleTrait;

	public function logout()
	{
		$this->c->getSession()->uns('uid');
		$this->c->getCookies()->remove('REMEMBERME');
	}

	protected function loginUser($id, $remember = false)
	{
		$this->c->getSession()->set('uid', $id);
		if ($remember) {
			$this->remember($id);
		}
	}

	protected function getAuthorisedUserId()
	{
		if ($id = $this->c->getSession()->get('uid')) {
			return $id;
		} elseif ($id = $this->getRememberId()) {
			$this->loginUser($id);
			return $id;
		}

		return null;
	}

	protected function getRememberId()
	{
		if ($this->c->getCookies()->has('REMEMBERME')) {
			$cookie = openssl_decrypt(
                base64_decode($this->c->getCookies()->get('REMEMBERME')),
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
		$this->c->getCookies()->set('REMEMBERME', base64_encode($login), 60*60*24*365);
	}

} 