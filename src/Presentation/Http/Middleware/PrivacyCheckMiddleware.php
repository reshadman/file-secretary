<?php

namespace Reshadman\FileSecretary\Presentation\Http\Middleware;

use Illuminate\Http\Request;
use Reshadman\FileSecretary\Application\CheckPrivacy;
use Reshadman\FileSecretary\Application\PrivacyCheckNeeds;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;

class PrivacyCheckMiddleware
{
    const REQUEST_PARAM_KEY = '_fs_privacy_needs';

    /**
     * @var FileSecretaryManager
     */
    private $fManager;
    /**
     * @var CheckPrivacy
     */
    private $checkPrivacyCommand;

    public function __construct(FileSecretaryManager $fManager, CheckPrivacy $checkPrivacyCommand)
    {
        $this->fManager = $fManager;
        $this->checkPrivacyCommand = $checkPrivacyCommand;
    }

    public function handle(Request $request, \Closure $next)
    {
        $contextData = $this->fManager->getContextData($contextName = $request->route('context_name'));

        if ($contextData === null) {
            abort(404);
        }

        $requestedFolder = $request->route('context_folder');

        if ($requestedFolder !== $contextData['context_folder']) {
            abort(404);
        }

        $privacyNeeds = new PrivacyCheckNeeds(
            $contextName,
            $contextData['context_folder'],
            $request->route('after_context_path')
        );

        // We merge the privacy needs to the request so the user can use it easily, If needed.
        $request->merge([static::REQUEST_PARAM_KEY => $privacyNeeds]);

        // If the config says to check the privacy we will perform the check
        if ($this->fManager->getConfig("check_privacy", true)) {
            $allowed = $this->checkPrivacyCommand->execute($privacyNeeds);
            if (!$allowed) {
                abort(400);
            }
        }

        return $next($request);
    }
}