<?php

namespace Phpsa\LaravelApiController\Contracts;

use Gate;

trait Policies
{
    /**
     * Qualifies the collection query to allow you to add params vai the policy
     * ie to limit to a specific user id mapping.
     */
    protected function qualifyCollectionQuery(): void
    {
        $user = auth()->user();
        $modelPolicy = Gate::getPolicyFor(self::$model);

        if ($modelPolicy && method_exists($modelPolicy, 'qualifyCollectionQueryWithUser')) {
            $modelPolicy->qualifyCollectionQueryWithUser($user, $this->repository);
        }
    }

    /**
     * Qualifies the collection query to allow you to add params vai the policy
     * ie to limit to a specific user id mapping
     * This may be overkill but could be usedfull ?
     */
    protected function qualifyItemQuery(): void
    {
        $user = auth()->user();
        $modelPolicy = Gate::getPolicyFor(self::$model);

        if ($modelPolicy && method_exists($modelPolicy, 'qualifyItemQueryWithUser')) {
            $modelPolicy->qualifyItemQueryWithUser($user, $this->repository);
        }
    }

    /**
     * Allows you to massage the data when creating a new record.
     *
     * @param array $data
     *
     * @return array
     */
    protected function qualifyStoreQuery(array $data): array
    {
        $user = auth()->user();
        $modelPolicy = Gate::getPolicyFor(self::$model);

        if ($modelPolicy && method_exists($modelPolicy, 'qualifyStoreDataWithUser')) {
            $data = $modelPolicy->qualifyStoreDataWithUser($user, $data);
        }

        return $data;
    }

    /**
     * Allows you to massage the data when updating an existing record.
     *
     * @param array $data
     *
     * @return array
     */
    protected function qualifyUpdateQuery(array $data): array
    {
        $user = auth()->user();
        $modelPolicy = Gate::getPolicyFor(self::$model);

        if ($modelPolicy && method_exists($modelPolicy, 'qualifyUpdateDataWithUser')) {
            $data = $modelPolicy->qualifyUpdateDataWithUser($user, $data);
        }

        return $data;
    }

    /**
     * Checks if the user has access to an ability.
     *
     * @param string $ability
     * @param mixed $arguments
     *
     * @return bool
     */
    protected function authoriseUserAction(string $ability, $arguments = null): bool
    {
        if (! $this->testUserPolicyAction($ability, $arguments)) {
            abort(401, 'Unauthorised');
        }

        return true;
    }

    protected function testUserPolicyAction(string $ability, $arguments = null): bool
    {
        $user = auth()->user();

        // If no arguments are specified, set it to the controller's model (default)
        if ($arguments === null) {
            $arguments = self::$model;
        }

        // Get policy for model
        if (is_array($arguments)) {
            $model = reset($arguments);
        } else {
            $model = $arguments;
        }

        $modelPolicy = Gate::getPolicyFor($model);
        // If no policy exists for this model, then there's nothing to check
        if (is_null($modelPolicy)) {
            return true;
        }

        // Check if the authenticated user has the required ability for the model
        if ($user->can($ability, $arguments)) {
            return true;
        }

        return false;
    }
}