$exceptions->render(function (HttpException $exception, Request $request) {
    return ($request->expectsJson())
        ? response()->json(['error' => $exception->getMessage()], $exception->getStatusCode())
        : null;
});