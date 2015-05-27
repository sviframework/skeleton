<?php

namespace Sv\BaseBundle;

abstract class UserManager extends ContainerAware
{

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
			$cookie = mcrypt_decrypt(MCRYPT_BLOWFISH, $this->getParameter('secret'), base64_decode($this->c->getCookies()->get('REMEMBERME')), MCRYPT_MODE_ECB);
			$data = explode('[|]', $cookie);
			return $data[1];
		}

		return null;
	}

	protected function remember($id)
	{
		$login = mcrypt_encrypt(
			MCRYPT_BLOWFISH,
			$this->getParameter('secret'),
			time() . '[|]' . $id . '[|]' . microtime(),
			MCRYPT_MODE_ECB);
		$this->c->getCookies()->set('REMEMBERME', base64_encode($login), 60*60*24*365);
	}

} 