<?php

namespace App\Http\Controllers\admin;

use App\Codec;
use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Repository\Helper\Response;
use App\Repository\Services\CodecService;
use Exception;
use Illuminate\Http\Request;

class CodecController extends Controller
{

    protected $codecService;

    public function __construct(CodecService $codecService)
    {
        $this->codecService = $codecService;

        $this->middleware('can:update,codec')->only(['update']);
        $this->middleware('can:delete,codec')->only(['delete']);
        $this->middleware('can:create,App\Codec')->only(['create']);

    }

    /**
     * @param Request $request
     * @return array
     * @throws GeneralException
     */
    public function create(Request $request)
    {
        $this->codecService->validateCreateCodec($request);
        $codec = $this->codecService->insertCodec($request);
        return Response::body(compact('codec'));
    }


    /**
     * @return array
     */
    public function list()
    {
        $codecs = Codec::where('global', true)->get();
        return Response::body(compact('codecs'));
    }

    /**
     * @param Codec $codec
     * @return array
     * @throws Exception
     */
    public function delete(Codec $codec)
    {
        if ($codec['global'])
            $codec->delete();
        return Response::body(['success' => true]);
    }

    /**
     * @param Codec $codec
     * @return array
     * @throws Exception
     */
    public function get(Codec $codec)
    {
        return Response::body(compact('codec'));
    }

    /**
     * @param Codec $codec
     * @param Request $request
     * @return array
     * @throws GeneralException
     */
    public function update(Codec $codec, Request $request)
    {
        $this->codecService->validateCreateCodec($request);
        $codec = $this->codecService->updateCodec($request, $codec);
        return Response::body(compact('codec'));
    }
}
