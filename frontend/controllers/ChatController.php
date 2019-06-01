<?php

namespace frontend\controllers;

use Yii;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use common\models\Chat;
use common\models\ChatUser;
use common\models\ChatMessage;
use common\models\ChatMessageStatus;
use common\models\ChatBlackList;
use common\models\User;
use common\models\Master;
use common\models\Manager;
use common\models\Klient;

use common\models\AuthAssignment;
use common\models\AuthItem;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use frontend\models\ChatImageForm;
use yii\helpers\FileHelper;

use common\models\Message;

/**
 * ChatController implements the CRUD actions for Chat model.
 */
class ChatController extends Controller
{
    const STATUS_ACCEPT = 1;
    const STATUS_ERROR = 0;
    
    const ERROR_USER_NAME = 1001;    
    const ERROR_WRITE_BASE = 1002;
    const ERROR_SEND_MESSAGE = 1003; 
    const ERROR_SEND_PARAMETR = 1004;
    
    const OP_STATUS_MESSAGE = 101;
    const OP_INPUT_MESSAGE = 102;
    const OP_OUTPUT_MESSAGE = 103;
    const OP_SET_USER_NAME = 104;  
    const OP_LIST_USERS = 105;
    const OP_CREATE_NEW_CHAT = 106;
    const OP_WRITEN = 107;
    const OP_SYSTEM = 108;
    const OP_ERROR_NAME = 109;
    const OP_SEARCH_USER = 110;
    const OP_GET_CHATS = 111;
    const OP_GET_HISTORY_MESSAGE = 112;
    const OP_EXIT_CHAT = 113;
    const OP_REMOVE_USER = 114;
    const OP_ADD_USER = 115;
    const OP_REMOVE_CHAT = 116;
    const OP_BLOCK_USERS = 117;
    const OP_UNLOOCK_USERS = 118;
    const OP_BLACK_LIST_USERS = 119;
    
    
    const ZAKAZ_AKTIVATE = 150;
    const ZAKAZ_DIAKTIVATE = 151;
   
    const NULL_MESSAGES = 1008;    
    const MESSAGE_ALL = 1010;
    
    const MESSAGE_SEND = 111;
    const MESSAGE_DELIVERED = 112;
    const MESSAGE_READED = 113;
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),             
                //'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['error', 'myjson', 'mystr'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [                        
                        'actions' => ['index', 'history-message', 'create-chat', 
                          'add-user', 'delete-user-from-chat', 'remove-chat', 'exit-chat', 'search-user', 
                            'list-chat-users', 'block-users', 'unlock-users', 'black-list-users', 'save-file'],
                        'allow' => true,                                                        
                        'roles' => ['@'],
                    ],                
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    
    public function actionSaveFile()
    {   
        $model = new ChatImageForm();
        $massFiles = UploadedFile::getInstancesByName('file');
        if ($massFiles == null) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Файлы не загружены']);  
        }
        $model->file = $massFiles[0];
        $model->id_chat = Yii::$app->request->post('id_chat');
          
        if (($name = $model->saveFile()) != null){            
            return json_encode(['status' => self::STATUS_ACCEPT, 'id_chat' => $model->id_chat, 'file' => $name]);
        } else return json_encode(['status' => self::STATUS_ERROR, 's_message' => $model->error]);           
       
        return json_encode(['status' => self::STATUS_ERROR, 's_message' => $model->error, '$_FILES' => $_FILES]);
    }

        /**
     * Lists all Chat models.
     * @return mixed
     */
    public function actionIndex()
    {      
        if (Yii::$app->user->isGuest) return $this->redirect ('/site/login');
        
        $blackList = ChatBlackList::find()->select(['locked'])
                ->where(['blocking' => Yii::$app->user->getId()])->asArray()->all();
        
        $blocked = '';
        foreach ($blackList as $one) {
            $blocked = $blocked . $one['locked'] . ', ';
        }
        if ($blocked != '') { 
            $blocked = ' AND u.id_user IN (' . substr ($blocked, 0, strlen ($blocked)-2) . ')';
        }
        
        try {
            $chats = Yii::$app->db->createCommand('SELECT id, autor, alias, status FROM chat c, chat_user u '
                    . ' WHERE c.id=u.id_chat AND c.status <> "' . Chat::CHAT_DELETED . '" AND u.id_user=' 
                    . Yii::$app->user->getId())->queryAll();
            
            if (count($chats) == 0){
                return $this->render('index', ['chats' => []]);
            }
            $idChats = ArrayHelper::getColumn($chats, 'id');    
            
            $messages = ChatMessage::findBySql('SELECT * FROM chat_message WHERE (id_chat, date, time) in 
                    (
                    select id_chat, date, MAX(time) from chat_message c
                    where (id_chat, date) in
                        (
                        select id_chat, max(date)
                        from chat_message WHERE id_chat IN ('. implode(', ', $idChats) .')
                        group by id_chat
                        )
                    GROUP BY id_chat
                    )')
                    ->indexBy('id_chat')
                    ->asArray()->all();
          
            $massUs = ArrayHelper::getColumn($messages, 'id_user');            
            $users = User::find()->select(['id', 'username'])->where(['id' => $massUs])->indexBy('id')->asArray()->all();
                        
        } catch (Exception $ex) {
            return json_encode(["status" => self::STATUS_ERROR, "s_message" => 'Ошибка поиска в бд']);
        }
        
        /**/
        $master = Yii::$app->db->createCommand('SELECT id_master, CONCAT(familiya, " ", imya, " "'
                        . ', otchestvo) AS fio FROM master WHERE id_master=' . 25)->queryOne();
                
        $managerId = Yii::$app->db->createCommand('SELECT mg.id_manager FROM `manager` mg, master m, auth_assignment a '
                . ' WHERE m.id_region=mg.id_region AND a.user_id=mg.id_manager AND a.item_name="'
                . AuthItem::MANAGER . '" AND m.id_master=' . 25)->queryScalar();

        $chatId = Yii::$app->db->createCommand('SELECT c.id_chat FROM chat_user c LEFT JOIN chat_user u '
                . 'ON c.id_chat=c.id_chat WHERE c.id_user=0 AND u.id_user=' . $managerId)->queryScalar();

        $date = date('Y-m-d');
        
        $chat = new Chat();
        $chat->autor = 0;
        $chat->alias = 'System';
        $chat->create_at = $date;
        $chat->status = Chat::CHAT_ACTIVE;

        $chat->save();
        
        $message = new ChatMessage();
        
        $message->id_chat = ($chatId != NULL) ? $chatId : $chat->id;
        $message->id_user = 0;
        
        $strM = 'Запрос мастера №'. $master['id_master'] 
                   . ' '. $master['fio'] . ' на взятие заявки №' . 1 ;
               
          
        $message->message = $strM;
        $message->date = $date;
        $message->time = date('H:i:s'); 
        $flag = ($message->validate() && $message->save());
        
        /**/
        return $this->render('index', [
            'chats' => $chats,
            'messages' => $messages,
            'users' => $users,  
            'res' => [$chatId, $chat->id, $flag, $chat, $message]
        ]);       
    }
    
     /*   
    public function actionUnlockUsers()
    {
        if (!$idUsers = Yii::$app->request->post('users')) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']);
        }
        
        if (!count($idUsers) > 0) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не выбран ни один пользователь']);
        }
        $id = Yii::$app->user->getId();
        
        foreach ($idUsers as $one) {
            $model = ChatBlackList::find()->where(['blocking' => $id, 'locked' => $one])->one();
            try {
                if ($model) $model->delete();
            } catch (Exception $ex) {               
            }
        }
        return json_encode(['status' => self::STATUS_ACCEPT, 's_message' => 'Пользователи разблокированны']);
    }

    
    public function actionBlockUsers()
    {
        if (!$idUsers = Yii::$app->request->post('users')) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']);
        }
        
        if (!count($idUsers) > 0) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не выбран ни один пользователь']);
        }
        $id = Yii::$app->user->getId();
        foreach ($idUsers as $one){
            if ($one == $id) 
                return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Вы не можете заблокировать самого себя']);
        }
        
        $date = date('Y-m-d');
        $time = date('H:i:s');
        try {
            foreach ($idUsers as $one) {
                Yii::$app->db->createCommand()->upsert('chat_black_list', 
                        ['blocking' => $id, 'locked' => $one, 'date' => $date, 'time' => $time])->execute();
            }
        } catch (Exception $ex) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка базы данных']);
        }
        
        return json_encode(['status' => self::STATUS_ACCEPT, 's_message' => 'Пользователи успешно добавлены в черный список']);
    }

    */
   /* public function actionHistoryMessage()
    {
        if (!Yii::$app->request->isAjax) return $this->redirect ('/site/login');
        
        $id = Yii::$app->request->post('id') ?? null;
        if (!$id) return json_encode(["status" => self::STATUS_ERROR, "s_message" => "Неверные входные данные"]);
        
        $date = Yii::$app->request->post('date') ?? null;
        
        if ($date != null) {
            $date = ChatMessage::find()->select(['date'])->where(['id_chat' => $id])
                    ->andWhere('date <"' . $date . '"')->orderBy(['date' => SORT_DESC])->scalar();
            if ($date == null) {
                return json_encode(["status" => self::STATUS_ERROR, "s_code" => self::NULL_MESSAGES, "s_message" => "Больше нет сообщений"]);
            }
            $date = ['chat_message.date' => $date];
            $orderBy = ['chat_message.time' => SORT_DESC];
        } else { 
            $date = ' chat_message.date IN (SELECT MAX(date) FROM chat_message WHERE id_chat='. $id .')';
            $orderBy = ['chat_message.time' => SORT_ASC];
        }
        
        $update = Yii::$app->db->createCommand('SELECT ms.id_message FROM chat_message_status ms '
                . 'LEFT JOIN chat_message m on m.id=ms.id_message where ms.id_user=' . Yii::$app->user->getId()
                . ' AND m.id_chat='. $id .' AND ms.id_user <> m.id_user AND status_message <> "'
                . ChatMessageStatus::MESSAGE_READED .'"')->queryAll();
        
        $update = ArrayHelper::getColumn($update, 'id_message');
        if (count($update) > 0) { 
            ChatMessageStatus::updateAll(['status_message' => ChatMessageStatus::MESSAGE_READED],
                ['id_message' => $update,
                    'id_user' => Yii::$app->user->getId()]);
        }
       
        $model = ChatMessage::find()->select([
                'id' => 'chat_message.id', 'id_chat', 'id_user' => 'chat_message.id_user',
                'parent_id', 'message', 'file', 'date' => 'chat_message.date',
                'time' => 'chat_message.time', 'autor' => 'user.username', 'status_message'
            ])->where(['id_chat' => $id])
                ->andWhere($date)
                ->orderBy($orderBy)
                ->join('INNER JOIN', 'user', 'chat_message.id_user=user.id')
                ->join('INNER JOIN', 'chat_message_status', 
                        'chat_message_status.id_message=chat_message.id '
                        . 'AND chat_message_status.id_user=chat_message.id_user')
                ->asArray()->all();
     
        return json_encode(["status" => self::STATUS_ACCEPT, "chat" => $model, "id" => Yii::$app->user->getId()]);
    }

    
/*    public function actionSearchUser(){
                   
        if (!Yii::$app->request->isAjax) return;
     
        $search = Yii::$app->request->post('search');
        
        if (count($search) > 0) { 
            $result = [];
            
            $where = 'username LIKE "%' . $search['username'] . '%" AND phone LIKE "%' . $search['phone'] 
                    . '%" AND familiya LIKE "%' . $search['familiya'] 
                    . '%" AND imya LIKE "%' . $search['imya'] 
                    . '%" AND otchestvo LIKE "%' . $search['otchestvo'] . '%"';
            $where1 = 'username LIKE "%' . $search['username'] 
                    . '%" AND (phone1 LIKE "%' . $search['phone'] 
                    . '%" OR phone2 LIKE "%' . $search['phone'] 
                    . '%" OR phone3 LIKE "%' . $search['phone'] 
                    . '%") AND familiya LIKE "%' . $search['familiya'] 
                    . '%" AND imya LIKE "%' . $search['imya'] 
                    . '%" AND otchestvo LIKE "%' . $search['otchestvo'] . '%"';           

            $listUsers = Master::find()->select(['username', 'id' => 'id_master', 'familiya', 'imya', 'otchestvo'])
                    ->where($where)->join('INNER JOIN', 'user', 'user.id=master.id_master')
                    ->asArray()->all();          
            foreach ($listUsers as $one) $result[] = $one;
          
            $listUsers = Manager::find()->select(['username', 'id' => 'id_manager', 'familiya', 'imya', 'otchestvo'])
                    ->where($where1)->join('INNER JOIN', 'user', 'user.id=manager.id_manager')
                    ->asArray()->all();
            foreach ($listUsers as $one) $result[] = $one;
          
            $listUsers = Klient::find()->select(['username', 'id' => 'id_klient', 'familiya', 'imya', 'otchestvo'])
                    ->where($where)->join('INNER JOIN', 'user', 'user.id=klient.id_klient')
                    ->asArray()->all();
            foreach ($listUsers as $one) $result[] = $one;
         
            
            if (count($result) > 0) 
                return json_encode(["status" => self::STATUS_ACCEPT, 
                            "users" => $result, 'id' => Yii::$app->user->getId()]);
        } 
        
        return json_encode(["status" => self::STATUS_ERROR, "s_message" => 'Не найдено совпадений']);
    }*/
    
   /*
    public function actionRemoveChat()
    {
        if (!$id = Yii::$app->request->post('id')) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']);
        }
        
        $model = Chat::find()->where('id=:id', [':id' => $id])->one();
        
        if (!$model) return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Чат не найден']);
        
        if ($model->autor != Yii::$app->user->getId()) 
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Удаление не возможно, вы не являетесь автором чата']);
        
        $model->status = Chat::CHAT_DELETED;
        if ($model->save()) {
            return json_encode(['status' => self::STATUS_ACCEPT, 's_message' => 'Чат удален']);
        }    
        
        return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка базы данных, повторите операцию']);
    }

    
    public function actionExitChat()
    {
        if (!$id = Yii::$app->request->post('id')) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']);
        }
        $myId = Yii::$app->user->getId();
        
        if(!$user = ChatUser::findOne(['id_chat' => $id, 'id_user' => $myId]))
                return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Вы не участвуете в этом чате']);
        
        $listUsers = ChatUser::find()->where('id_chat=' . $id . ' AND id_user <> ' . $myId)->asArray()->all();
        
        $idNewAutor = Yii::$app->request->post('id_user') ?? null;
        $chat = Chat::findOne(['id' => $id]);
        
        if (count($listUsers) == 1){
            $chat->status = Chat::CHAT_DELETED;
            if ($chat->save()){
                return json_encode(['status' => self::STATUS_ACCEPT, 's_message' => 'В чате всего два пользователя, вследстии чего чат удален']);
            }
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка базы данных, повторите операцию']);
        }
        
        if ($chat->autor == $myId) {
            if ($idNewAutor) {
                $chat->autor = $idNewAutor;
            } else {
                $chat->autor = $listUsers[0]['id_user'];
            }
            if (!$chat->save()){
                return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Операция прервана, повторите']);
            }              
        } 
        
        if (!$user->delete()) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Операция прервана, повторите1']);
        }
        
        return json_encode(['status' => self::STATUS_ACCEPT, 's_message' => 'Операция выполнена']);
    }
    
    
    public function actionDeleteUserFromChat()
    {        
        if (!$id = Yii::$app->request->post('id')) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']);
        }
        if (Chat::find()->select(['autor'])->where(['id' => $id])->scalar() != Yii::$app->user->getId()) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Вы не являетесь автором чата']);
        }
        if (ChatUser::find()->where(['id_chat' => $id])->count() <= 2) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Удаление не возможно. В чате всего два пользователя, удалите чат целиком']);
        }
        if (!$users = Yii::$app->request->post('users')) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error1']);
        }   
        
        try {
            foreach ($users as $one) {
                $us = ChatUser::find()->where(['id_chat' => $id, 'id_user' => $one])->limit(1)->one();
                if (!$us->delete()) {
                    return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Некоторые пользователи не были удалены']);
                }
            }
        } catch (\Exception $ex) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка базы данных']);
        }
        
        return json_encode(['status' => self::STATUS_ACCEPT, 's_message' => 'Пользователи успешно удалены']);        
    }
    
    
    public function actionAddUser()
    {
        if (!$id = Yii::$app->request->post('id')) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']);
        }
        if (Chat::find()->select(['autor'])->where(['id' => $id])->scalar() != Yii::$app->user->getId()) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Вы не являетесь автором чата']);
        }
        if (!$users = Yii::$app->request->post('users')) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error1']);
        }
        foreach ($users as $one) {
            $user = User::find()->select(['id'])->where(['id' => $one])->limit(1)->one();            
            if (!$user) return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']);
        }
        
        try {
            foreach ($users as $one) {
                if (!ChatUser::find()->where(['id_chat' => $id, 'id_user' => $one])->limit(1)->asArray()->one()) {
                    Yii::$app->db->createCommand()
                        ->insert('chat_user', ['id_chat' => $id, 'id_user' => $one])->execute();
                }
            }
        } catch (\Exception $ex) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка записи в базу данных']);
        }
        
        return json_encode(['status' => self::STATUS_ACCEPT, 's_message' => 'Пользователи успешно добавлены в чат']);
    }
    
    
    public function actionBlackListUsers()
    {
        $lockUsers = ChatBlackList::find()->select(['locked'])
                ->where(['blocking' => Yii::$app->user->getId()])->asArray()->all();
        
        if (!$lockUsers){
            return json_encode(['status' => self::STATUS_ACCEPT, 'users' => [], 's_message' => 'Черный список пуст']);
        }
        
        $usersId = ArrayHelper::getColumn($lockUsers, 'locked');
        
        $roles = AuthAssignment::find()->where(['user_id' => $usersId])->asArray()->all();
        
        try {
            foreach ($roles as $key => $one){
                switch ($one['item_name']){
                    case AuthItem::HEAD_MANAGER:
                    case AuthItem::MANAGER: $table = 'manager';
                        $field = 'id_manager';
                        break;
                    case AuthItem::MASTER: $table = 'master';
                        $field = 'id_master';
                        break;
                    case AuthItem::KLIENT: $table = 'klient';
                        $field = 'id_klient';                    
                        break;
                    default: return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не опознанный пользователь!!!']);
                }
                $query = Yii::$app->db->createCommand('SELECT username, CONCAT(p.familiya, " ", p.imya, " ", p.otchestvo) AS fio '
                        . 'FROM '. $table .' p, user u WHERE p.'. $field .'=u.id AND u.id=' . $one['user_id'])->queryOne();

                $roles[$key]['fio'] = $query['fio'];
                $roles[$key]['username'] = $query['username']; 
            }
        } catch (\Exception $ex) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка поиска в бд']);
        }
        
        return json_encode(['status' => self::STATUS_ACCEPT, 'users' => $roles]);
    }
        
        
    public function actionListChatUsers()
    {
        
        if (!$id = Yii::$app->request->post('id')) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Parametr error']);
        }
        try {
            $users = Yii::$app->db->createCommand('SELECT id_user, autor FROM chat_user u, chat c WHERE u.id_chat=c.id AND id_chat=' . $id)->queryAll();

            $usersId = ArrayHelper::getColumn($users, 'id_user');

            $roles = AuthAssignment::find()->where(['user_id' => $usersId])->asArray()->all();
            
            reset($roles);
            while ($one = current($roles)){
                switch ($one['item_name']){
                    case AuthItem::HEAD_MANAGER:
                    case AuthItem::MANAGER: $table = 'manager';
                        $field = 'id_manager';
                        break;
                    case AuthItem::MASTER: $table = 'master';
                        $field = 'id_master';
                        break;
                    case AuthItem::KLIENT: $table = 'klient';
                        $field = 'id_klient';                    
                        break;
                    default: return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Не опознанный пользователь!!!']);
                }
                $query = Yii::$app->db->createCommand('SELECT username, CONCAT(p.familiya, " ", p.imya, " ", p.otchestvo) AS fio '
                        . 'FROM '. $table .' p, user u WHERE p.'. $field .'=u.id AND u.id=' . $one['user_id'])->queryOne();

                $roles[key($roles)]['fio'] = $query['fio'];
                $roles[key($roles)]['username'] = $query['username']; 
                
                next($roles);
            }
            
            $blocked = ChatBlackList::find()->where(['blocking' => Yii::$app->user->getId(), 'locked' => $usersId])->asArray()->all();
            
        } catch (\Exception $ex) {
            return json_encode(['status' => self::STATUS_ERROR, 's_message' => 'Ошибка поиска в бд']);
        }
        
        return json_encode(['status' => self::STATUS_ACCEPT, 'chat_users' => $roles, 'black_list' => $blocked, 'id' => Yii::$app->user->getId(), 'autor' => $users[key($users)]['autor']]);
    }
    

    /**
     * Finds the Chat model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Chat the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Chat::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
