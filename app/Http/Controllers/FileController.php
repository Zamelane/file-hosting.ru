<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileStoreRequest;
use App\Models\Right;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Validator;


class FileController extends Controller
{
    public function store(FileStoreRequest $request) {
        $pathToUpload = 'uploads/' . $request->user()->id . '/';
        $filesUploaded = [];
        $user = auth()->user();

        // Если файлов не загрузили, то вылетаем из функции
        // Файлы точно есть. См. FileStoreRequest
        if (!$request->hasFile("files")) {
            return;
        }

         // Получаем файлы, которые загружаются на наш сервер
        $files = $request->file('files');

        // Текущий адрес
        $protocol = "http" . ($request->secure() ? "s" : "") . "://";
        $host = $request->getHttpHost();

        // Перебираем каждый из них
        foreach ($files as $file) {
            // Проверяем размер и т.п. из FileStoreRequest для файла
            if (!$file->isValid()) {
                continue;
            }

            $fileName;
            $fileId = Str::random(10);
            $extension;
            try {
                // Расширение файла
                $extension = $file->extension();
                // Имя файла без расширения
                $originalName = substr($file->getClientOriginalName(), 0, -(mb_strlen($extension) + 1));
                // Имя файла для сохранения
                $fileName = $originalName;
                // Итоговое имя файла с расширением
                $searchName = $originalName . ".$extension";
                // Постфикс
                $number = 1;
                // Значение (I) на конце, если файл есть
                while (file_exists($pathToUpload . $searchName)) {
                    // Готовим новое имя файла
                    $fileName = $originalName . " ($number)";
                    // Готовим новое имя файла с расширением
                    $searchName = $fileName . "." . $extension;
                    $number++;
                }
                // Сохраняем файл
                $file->move($pathToUpload, $searchName);
                // Заносим в базу
                File::create([
                    "name" => $fileName,
                    "extension" => $extension,
                    "path" => $pathToUpload,
                    "file_id" => $fileId,
                    "user_id" => $user->id
                ]);
            } catch (Exception $e) {
                // Если ошибка - добавляем сообщение о ней
                array_push($filesUploaded, [
                    "success" => false,
                    "message" => $e->getMessage(),
                    "name" => $file->getClientOriginalName()
                ]);
                continue;
            }
            
            // Пусть до файла на основе текущего адреса
            $url = $protocol . $host . "/files/" . $fileId;

            // Если успешно - сохраняем
            array_push($filesUploaded, [
                "success" => true,
                "code" => 200,
                "message" => "success",
                "name" => $fileName,
                "url" => $url,
                "file_id" => $fileId,
            ]);
        }

        return response()->json($filesUploaded)->setStatusCode(200);
    }

    public function download($id) {
        $file = File::where("file_id", "=", $id)->first();
        $path = "";

        // Если запись о файле в базе найдена
        if ($file) {
            // Собираем путь до файла
            $path = $file->path;
            $path .= $file->name . '.';
            $path .= $file->extension;
        }

        // Если файла нет в базе или на диске,
        // то выкидываем ошибку
        if (!$file || !file_exists($path)) {
            return response()->json([
                "message" => "Not found",
                "code"=> 404
            ])->setStatusCode(404);
        }

        // Проверяем права доступа
        $user = auth()->user();
        $right = Right::where("file_id", "=", $file->id)
            ->where("user_id", "=", $user->id)->first();

        // Если обращается не автор файла и нет прав
        // доступа (не поделились файлом)
        if ($user->id != $file->user_id && !$right) {
            return response()->json([
                "message" => "Forbidden for you"
            ])->setStatusCode(403);
        }

        // Отдаём файл на скачивание
        return response()->download($path, basename($path));
    }
    public function edit(Request $request, $id) {

        // Првоеряем поля через валидацию
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:1|max:255',
        ]);

        // Если есть ошибки, то выводим
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // Новое имя файла
        $name = $request->name;
        
        // Ищем файл
        $file = File::where("file_id", "=", $id)->first();
        $path = "";
        $originalName = "";
        $extension = "";

        // Если запись о файле в базе найдена
        if ($file) {
            // Собираем путь до файла
            $path = $file->path;
            $originalName = $file->name;
            $extension .= "." . $file->extension;
        }

        // Если файла нет в базе или на диске,
        // то выкидываем ошибку
        if (!$file || !file_exists($path . $originalName . $extension)) {
            return response()->json([
                "message" => "Not found",
                "code"=> 404
            ])->setStatusCode(404);
        }

        // Проверяем права доступа
        $user = auth()->user();

        if ($user->id != $file->user_id) {
            return response()->json([
                "message" => "Forbidden for you"
            ])->setStatusCode(403);
        }

        // Переименовываем файл
        rename($path . $originalName . $extension, $path . $name . $extension);
        File::where("file_id","=", $id)->update([
            "name" => $name
        ]);

        return response()->json([
            "success" => true,
            "code" => 200,
            "message" => "Renamed"
        ]);
    }
    public function destroy($id) {
        // Ищем файл
        $file = File::where("file_id", "=", $id)->first();
        $path = "";

        // Если запись о файле в базе найдена
        if ($file) {
            // Собираем путь до файла
            $path = $file->path;
            $path .= $file->name . '.';
            $path .= $file->extension;
        }

        // Если файла нет в базе или на диске,
        // то выкидываем ошибку
        if (!$file || !file_exists($path)) {
            return response()->json([
                "message" => "Not found",
                "code"=> 404
            ])->setStatusCode(404);
        }

        // Проверяем права доступа
        $user = auth()->user();

        if ($user->id != $file->user_id) {
            return response()->json([
                "message" => "Forbidden for you"
            ])->setStatusCode(403);
        }

        // Удаляем файл с диска
        unlink($path);

        // Удаляем запись о файле из базы
        $file->delete();

        return response()->json([
            "success" => true,
            "code" => 200,
            "message" => "File deleted"
        ]);
    }

    public function owned(Request $request) {
        // Текущий адрес
        $protocol = "http" . ($request->secure() ? "s" : "") . "://";
        $host = $request->getHttpHost();

        $currUser = auth()->user();
        // Все файлы пользователя из базы
        $files = File::where("user_id", "=", $currUser->id)->get();

        $response = [];

        foreach ($files as $file) {
            // Пусть до файла на основе текущего адреса
            $url = $protocol . $host . "/files/" . $file->id;
            $access = [[
                "fullName" => $currUser->first_name,
                "email" => $currUser->email,
                "type" => "author"
            ]];

            $coauthors = Right::where("file_id", "=", $file->id)->get();

            foreach ($coauthors as $coauthor) {
                $coauthorUser = User::find($coauthor->user_id);
                array_push($access, [
                    "fullName" => $coauthorUser->first_name,
                    "email" => $coauthorUser->email,
                    "type" => "co-author"
                ]);
            }
            
            $response[] = [
                "file_id" => $file->file_id,
                "name" => $file->name,
                "code" => 200,
                "url" => $url,
                "access" => $access
            ];
        }

        return response()->json($response);
    }

    // Вернёт толькол те файлы, к которы пользователь имеет доступ.
    // Собственные файлы не вернёт, т.к. на свои файлы сами себе права не выдаём
    // (они и так в файле указаны)
    public function allowed(Request $request) {
        // Текущий адрес
        $protocol = "http" . ($request->secure() ? "s" : "") . "://";
        $host = $request->getHttpHost();

        $currUser = auth()->user();
        // Все файлы с доступом у текущего пользователя
        $rights = Right::where("user_id", "=", $currUser->id)->get();

        $response = [];

        foreach ($rights as $right) {
            $files = File::where("id", "=", $right->file_id)->get();
            foreach ($files as $file) {
                // Пусть до файла на основе текущего адреса
                $url = $protocol . $host . "/files/" . $file->id;
            
                $response[] = [
                    "file_id" => $file->file_id,
                    "name" => $file->name,
                    "code" => 200,
                    "url" => $url
                ];
            }
        }

        return response()->json($response);
    }
}