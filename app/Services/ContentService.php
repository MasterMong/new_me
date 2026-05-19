<?php

namespace App\Services;

use App\Models\ContentView;
use App\Models\ModuleContent;
use App\Models\User;

class ContentService
{
    /**
     * Check if a specific content is accessible by the user.
     */
    public function isContentAccessible(User $user, ModuleContent $content): bool
    {
        $module = $content->module;

        // If not sequential, it's always accessible (assuming module itself is unlocked)
        if (! ($module->is_sequential ?? false)) {
            return true;
        }

        // Get the immediately preceding content in the same module
        $previousContent = ModuleContent::query()
            ->where('module_id', $module->id)
            ->where('sort_order', '<', $content->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        // If there is no previous content, this is the first item, so it's accessible
        if (! $previousContent) {
            return true;
        }

        // Check if the previous content has been marked as completed for this user
        $progress = ContentView::query()
            ->where('user_id', $user->id)
            ->where('content_id', $previousContent->id)
            ->first();

        return $progress && $progress->is_completed;
    }

    /**
     * Get the next content in the module if the current one is completed.
     */
    public function getNextContent(ModuleContent $currentContent): ?ModuleContent
    {
        return ModuleContent::query()
            ->where('module_id', $currentContent->module_id)
            ->where('sort_order', '>', $currentContent->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();
    }
}
