# SkipList

Список пропусков - вероятностная структура данных, позволяющая за `O(log(n))` времени выполнять операции добавления, удаления, и поиска элементов.

## Node

```php
class Node{

    public ?Node $prev;
    public ?Node $next;
    public ?Node $below;
    public ?Node $above;

    public int $key;

    public function __construct(int $key){
        $this->key = $key;
        $this->prev = null;
        $this->next = null;
        $this->above = null;
        $this->below = null;
    }

}
```

Данная структура является многоуровневой. Каждый элемент должен ссылаться не только на предыдущий и следующий элементы, но а также на вышестоящий и нижестоящий элементы. Поле `$key` - значение данного узла.

## Структура SkipList

```php
public Node $head;
public Node $tail;

public int $NEG_INF = PHP_INT_MIN;
public int $POS_INF = PHP_INT_MAX;
public int $heightOfList = 0;

public function __construct() {
    $this->head = new Node($this->NEG_INF);
    $this->tail = new Node($this->POS_INF);
    $this->head->next = $this->tail;
    $this->tail->prev = $this->head;
}
```

В классе `SkipList` объявляем поля `$head` и `$tail`. По мере добавления уровней, всегда самый верхний уровень будет оставаться без узлов. Следующий элемент *головы* это *хвост* списка, а предыдущий элемент *хвоста* - это *голова* списка. У *головы* списка на всех уровнях будет наибольшое целое число, поддерживаемое в этой сборке PHP - `int(9223372036854775807) in 64 bit systems`. У *хвоста* списка наименьшое целое число - `int(-9223372036854775808) in 64 bit systems`.

### SkipSearch

```php
    public function skipSearch(int $key){
        $node = $this->head;


        while ($node->below != null){

            $node = $node->below;

            while($node->next->key <= $key){
                $node = $node->next;
            }

        }

        return $node;
    }
```