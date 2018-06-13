<?php
namespace Virtualorz\Contact;

use DB;
use Request;
use Validator;
use App\Exceptions\ValidateException;
use PDOException;
use Exception;
use Pagination;
use Config;
use Mail;

class Contact
{
    public function list($page = 0) {

        $page_display = intval(Request::input('page_display', 10));
        if (!in_array($page_display, Config::get('pagination.data_display', []))) {
            $page_display = Config::get('pagination.items');
        }

        $qb = DB::table('contact')
            ->select([
                'contact.id',
                'contact.created_at',
                'contact.name',
                'contact.company',
                'contact.tel',
                'contact.email',
                'contact.status'
            ])
            ->whereNull('contact.delete')
            ->orderBy('contact.created_at','DESC');
        if($page !== 0)
        {
            $qb->offset(($page - 1) * $page_display)
                ->limit($page_display);
        }
        $dataSet = $qb->get();

        $dataCount = $qb->cloneWithout(['columns', 'orders', 'limit', 'offset'])
                ->cloneWithoutBindings(['select', 'order'])
                ->count();
            
        Pagination::setPagination(['total'=>$dataCount]);

        return $dataSet;
    }

    public function add()
    {
        $validator = Validator::make(Request::all(), [
            'contact-name' => 'string|required|max:12',
            'contact-company' => 'string|required|max:24',
            'contact-tel' => 'string|required|max:10',
            'contact-email' => 'string|required|max:384',
            'contact-message' => 'string|required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
        }

        $dtNow = new \DateTime();

        DB::beginTransaction();
        try {

            $insert_id = DB::table('contact')
                ->insertGetId([
                    'created_at' => $dtNow,
                    'updated_at' => $dtNow,
                    'name' => Request::input('contact-name'),
                    'company' => Request::input('contact-company'),
                    'tel' => Request::input('contact-tel'),
                    'email' => Request::input('contact-email'),
                    'message' => Request::input('contact-message'),
                    'status' => 0,
                    'remark' => '',
                ]);
            Mail::send('Contact::email', [
                    'data' => ['title'=>'Contact from website','name'=>Request::input('contact-name'),'message'=>Request::input('contact-message')],
                        ], function ($m) {
                    $m->to(config('contact.admin_email'));
                    //$m->to('virtualorz@gmail.com');
                    $m->subject("Contact email notice");
            });

            DB::commit();

        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }
    }

    public function reply()
    {
        $validator = Validator::make(Request::all(), [
            'contact_reply-content' => 'string|required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
        }

        $dtNow = new \DateTime();
        $sn = 1;
        try {
            $dbh = DB::getPdo();
            $sth = $dbh->prepare('call `_get_sn_contact_reply_id` (?)');
            $sth->execute([Request::input('id')]);
            $result = [];
            do {
                try {
                    $tmpResult = $sth->fetchAll(\Config::get('database.fetch', \PDO::FETCH_OBJ));
                } catch (PDOException $ex) {
                    break;
                }
                
                $result[] = $tmpResult;
            } while ($sth->nextRowset());
            $sn = $result[0][0]->sn;
        } catch (\PDOException $ex) {
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }

        DB::beginTransaction();
        try {
            $dataRow = DB::table('contact')
                ->select([
                    'contact.email',
                    'contact.name'
                ])
                ->where('contact.id',Request::input('id'))
                ->first();
            
            DB::table('contact_reply')
                ->insert([
                    'contact_id' => Request::input('id'),
                    'id' => $sn,
                    'created_at' => $dtNow,
                    'updated_at' => $dtNow,
                    'content' => Request::input('contact_reply-content'),
                    'creat_admin_id' => Request::input('contact_reply-creat_admin_id', null),
                    'update_admin_id' => Request::input('contact_reply-update_admin_id', null),
                ]);
            DB::table('contact')
                ->where('id',Request::input('id'))
                ->update([
                    'status' => 2
                ]);
            
            Mail::send('Contact::email', [
                'data' => ['title'=>'Contact reply','name'=>$dataRow->name,'message'=>Request::input('contact_reply-content')],
                    ], function ($m) {
                $m->to($dataRow->email);
                $m->subject("Contact email notice");
            });

            DB::commit();

        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }
    }

    public function detail($id = '',$is_backend = 0)
    {
        $dataRow_faq = collect();
        try {
            if($is_backend == 1)
            {
                DB::table('contact')
                    ->where('id',$id)
                    ->where('status',0)
                    ->update([
                        'status' => 1
                    ]);
            }
            $dataRow_contact = DB::table('contact')
                ->select([
                    'contact.id',
                    'contact.created_at',
                    'contact.updated_at',
                    'contact.name',
                    'contact.company',
                    'contact.tel',
                    'contact.email',
                    'contact.message',
                    'contact.status'
                ])
                ->where('contact.id', $id)
                ->whereNull('contact.delete')
                ->first();
            if ($dataRow_contact != null) {
                $dataSet_reply = DB::table('contact_reply')
                    ->select([
                        'contact_reply.id',
                        'contact_reply.created_at',
                        'contact_reply.updated_at',
                        'contact_reply.content',
                    ])
                    ->where('contact_reply.contact_id', $dataRow_contact->id)
                    ->get();
                $dataRow_contact->reply = $dataSet_reply;
            }
        } catch (\PDOException $ex) {
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }

        return $dataRow_contact;
    }

    public function delete()
    {
        $validator = Validator::make(Request::all(), [
            'id' => 'required', //id可能是陣列可能不是
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
        }

        $ids = Request::input('id', []);
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $dtNow = new \DateTime();

        DB::beginTransaction();
        try {
            foreach ($ids as $k => $v) {

                DB::table('contact')
                    ->where('id', $v)
                    ->update([
                        'delete' => $dtNow,
                    ]);
            }

            DB::commit();
        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }
    }
}
