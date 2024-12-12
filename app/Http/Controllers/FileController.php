<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FileController extends Controller
{
    public function myFiles(string $folder = null)
    {
        if($folder){
            $folder = File::query()->where('created_by', Auth::id())
                                   ->where('path', $folder)
                                   ->firstOrFail();
        }
        if(!$folder){
            $folder = $this->getRoot();
        }
        $files = File::query()->where('parent_id', $folder->id)
                     ->where('created_by', Auth::id())
                     ->orderBy('is_folder', 'desc')
                     ->orderBy('created_at', 'desc')
                     ->paginate();

        $files = FileResource::collection($files);

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);

        $folder = new FileResource($folder);

        return Inertia::render('MyFiles', compact('files','folder', 'ancestors'));
    }

    public function createFolder(StoreFolderRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if(!$parent) {
            $parent = $this->getRoot();
        }

        $file = new File();
        $file->is_folder = 1;
        $file->name = $data['name'];

        $parent->appendNode($file);
    }

    public function store(StoreFileRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        $user = $request->user();
        $fileTree = $request->file_tree;

        if(!$parent){
            $parent = $this->getRoot();
        }

        if(!empty($fileTree)){
            $this->saveFileTree($fileTree, $parent, $user);
        } else {
            foreach ($data['files'] as $file) {
                /** @var UploadedFile $file $model */
                $path = $file->store('/files/'. $user->id);

                $model = new File();
                $model->storage_path = $path;
                $model->is_folder = false;
                $model->name = $file->getClientOriginalName();
                $model->mime = $file->getMimeType();
                $model->size = $file->getSize();
                $parent->appendNode($model);
            }
        }

    }

    public function getRoot()
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }
}
