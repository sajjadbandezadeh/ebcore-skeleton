<?php

namespace App\entities\User\Controllers;

use App\provider\mappers\UserMappers\UserIndexMapper;
use App\provider\response\CustomResponse;
use ebcore\framework\DB\DbContext;
use ebcore\framework\Module\Response;
use ebcore\framework\Packages\Dump\Dump;

class UserController {

    public function index() {
        $users = DbContext::User()->all();
        // Dump::dd($users);
        if (empty($users)) {
            Response::json(null, "No users found", 404, false);
        }
        return Response::json($users);
    }

    public function indexCustomResponse() {
        $users = DbContext::User()->all();
        if (empty($users)) {
            $response = new CustomResponse(null, "No users found");
            $response->json();
        }
        $response = new CustomResponse($users,"Done");
        $response->json();
    }

    public function indexCustomData()
    {
        $users = DbContext::User()->newQuery()->get();
        if (empty($users)) {
            return Response::json(null, "No users found", 404, false);
        }
        $mapped_users = UserIndexMapper::mapUserCollection($users);
        return Response::json($mapped_users);
    }

    public function getById($id)
    {
        $user = DbContext::User()->find($id);
        if (empty($user)) {
            return Response::json(null, "No user found", 404, false);
        }
        return Response::json($user);
    }

    public function create($name, $family)
    {
        $user = array();
        $user["name"] = $name;
        $user["family"] = $family;
        $user["created_at"] = date("Y/m/d h:i:sa");
        $created_user = DbContext::User()->create($user);
        return Response::json($created_user,"user created");
    }

    public function edit($id,$name)
    {
        $user = array();
        $user["name"] = $name;
        $created_user = DbContext::User()->update([$user,$id]);
        return Response::json($created_user,"user edited");
    }

    public function delete($id)
    {
        $user_exists = DbContext::User()->find($id);
        if (empty($user_exists)) {
            return Response::json(null, "No user found", 404, false);
        }
        $status = DbContext::User()->delete($id);
        if ($status) {
            return Response::json(null,"User deleted");
        }
        return Response::json(null, "Error", -1, false);
    }
}