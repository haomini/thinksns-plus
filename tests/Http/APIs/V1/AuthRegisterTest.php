<?php

namespace Ts\Test\Http\APIs\V1;

use App\Models\User;

class AuthRegisterTest extends TestCase
{
    // test API.
    protected $uri = '/api/v1/auth/register';

    // Set up create user.
    protected $user;

    // test user data.
    protected $phone = '18781994583';
    protected $password = 123456;
    protected $username = 'Seven_test_user';

    // send test request body.
    protected $requestBody = [];

    /**
     * Setup the test environment.
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function setUp()
    {
        parent::setUp();

        $phone = $this->phone;
        $name = $this->username;

        User::where('phone', $phone)
            ->orWhere('name', $name)
            ->withTrashed()
            ->forceDelete();

        // Create user.
        $user = new User();
        $user->phone = $phone;
        $user->name = $name;
        $user->email = '';
        $user->createPassword($this->password);
        $user->save();

        $this->user = $user;
        $this->requestBody = [
            'phone'       => $phone,
            'password'    => $this->password,
            'device_code' => 'testing',
        ];
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function tearDown()
    {
        // delete user.
        $this->user->forceDelete();

        parent::tearDown();
    }

    /**
     * 测试当设备号为空的时候的报错信息.
     *
     * message code: 1014
     *
     * test middleware \App\Http\Middleware\CheckDeviceCodeExisted
     *
     * @return [type] [description]
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    public function testCheckDeviceCodeNotExisted()
    {
        $requestBody = $this->requestBody;
        $requestBody['device_code'] = '';

        $this->postJson($this->uri, $requestBody);

        // Asserts that the status code of the response matches the given code.
        $this->seeStatusCode(422);

        // Assert that the response contains an exact JSON array.
        $json = $this->createMessageResponseBody([
            'code'    => 1014,
            'message' => '设备号不能为空',
        ]);
        $this->seeJsonEquals($json);
    }

    /**
     * 测试注册手机号为空.
     *
     * message code:1000
     * test middleware \App\Http\Middleware\VerifyPhoneNumber
     *
     * @author martinsun <syh@sunyonghong.com>
     */
    public function testCheckPhoneNotExisted()
    {
        $requestBody = $this->requestBody;
        $requestBody['phone'] = '';

        $this->postJson($this->uri, $requestBody);

        // Asserts that the status code of the response matches the given code.
        $this->seeStatusCode(403);
        // Assert that the response contains an exact JSON array.
        $json = $this->createMessageResponseBody([
            'code' => 1000,
        ]);
        $this->seeJsonEquals($json);
    }

    /**
     * 测试注册手机号非法.
     *
     * message code:1000
     * test middleware \App\Http\Middleware\VerifyPhoneNumber
     *
     * @author martinsun <syh@sunyonghong.com>
     */
    public function testCheckPhoneError()
    {
        $requestBody = $this->requestBody;
        $requestBody['phone'] = '123456789';

        $this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $this->seeStatusCode(403);
        // Assert that the response contains an exact JSON array.
        $json = $this->createMessageResponseBody([
            'code' => 1000,
        ]);
        $this->seeJsonEquals($json);
    }

    /**
     * 测试注册用户名不存在.
     *
     * message code:1000
     * test middleware \App\Http\Middleware\VerifyUserNameRole
     *
     * @author martinsun <syh@sunyonghong.com>
     */
    public function testCheckUserNameNotExisted()
    {
        $requestBody = $this->requestBody;

        $this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $this->seeStatusCode(403);
        // Assert that the response contains an exact JSON array.
        $json = $this->createMessageResponseBody([
            'code' => 1002,
        ]);
        $this->seeJsonEquals($json);
    }

    /**
     * 测试注册用户名长度.
     *
     * message code:
     * test middleware \App\Http\Middleware\VerifyUserNameRole
     *
     * @author martinsun <syh@sunyonghong.com>
     */
    public function testCheckUserNameLength()
    {
        $requestBody = $this->requestBody;
        $requestBody['name'] = 'iss';

        $this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $this->seeStatusCode(403);
        // Assert that the response contains an exact JSON array.
        $json = $this->createMessageResponseBody([
            'code' => 1002,
        ]);
        $this->seeJsonEquals($json);
    }

    /**
     * 测试注册用户名规则.
     *
     * message code:1003
     * test middleware \App\Http\Middleware\VerifyUserNameRole
     *
     * @author martinsun <syh@sunyonghong.com>
     */
    public function testCheckUserNameRole()
    {
        $requestBody = $this->requestBody;
        $requestBody['name'] = '++test';

        $this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $this->seeStatusCode(403);
        // Assert that the response contains an exact JSON array.
        $json = $this->createMessageResponseBody([
						'code' => 1003,
        ]);
        $this->seeJsonEquals($json);
    }

		/**
		 * 测试注册时用户名是否被占用
		 *
		 * message code:1004
		 * test middleware \App\Http\Middleware\CheckUserByNameNotExisted
		 *
		 * @author martinsun <syh@sunyonghong.com>
		 */
		public function testCheckUserNameUsed()
		{
				$requestBody = $this->requestBody;
				$requestBody['name'] = 'Seven_test_user';

				$this->postJson($this->uri, $requestBody);
        // Asserts that the status code of the response matches the given code.
        $this->seeStatusCode(403);
        // Assert that the response contains an exact JSON array.
        $json = $this->createMessageResponseBody([
						'code' => 1004,
        ]);
        $this->seeJsonEquals($json);
		}
}
