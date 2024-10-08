<?php

namespace HiEvents\DomainObjects\Generated;

/**
 * THIS FILE IS AUTOGENERATED - DO NOT EDIT IT DIRECTLY.
 * @package HiEvents\DomainObjects\Generated
 */
abstract class TicketsCheckInListDomainObjectAbstract extends \HiEvents\DomainObjects\AbstractDomainObject
{
    final public const SINGULAR_NAME = 'tickets_check_in_list';
    final public const PLURAL_NAME = 'tickets_check_in_lists';
    final public const ID = 'id';
    final public const TICKET_ID = 'ticket_id';
    final public const CHECK_IN_LIST_ID = 'check_in_list_id';
    final public const DELETED_AT = 'deleted_at';

    protected int $id;
    protected int $ticket_id;
    protected int $check_in_list_id;
    protected ?string $deleted_at = null;

    public function toArray(): array
    {
        return [
                    'id' => $this->id ?? null,
                    'ticket_id' => $this->ticket_id ?? null,
                    'check_in_list_id' => $this->check_in_list_id ?? null,
                    'deleted_at' => $this->deleted_at ?? null,
                ];
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTicketId(int $ticket_id): self
    {
        $this->ticket_id = $ticket_id;
        return $this;
    }

    public function getTicketId(): int
    {
        return $this->ticket_id;
    }

    public function setCheckInListId(int $check_in_list_id): self
    {
        $this->check_in_list_id = $check_in_list_id;
        return $this;
    }

    public function getCheckInListId(): int
    {
        return $this->check_in_list_id;
    }

    public function setDeletedAt(?string $deleted_at): self
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deleted_at;
    }
}
