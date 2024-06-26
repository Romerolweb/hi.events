<?php

namespace HiEvents\DomainObjects\Generated;

/**
 * THIS FILE IS AUTOGENERATED - DO NOT EDIT IT DIRECTLY.
 * @package HiEvents\DomainObjects\Generated
 */
abstract class PasswordResetDomainObjectAbstract extends \HiEvents\DomainObjects\AbstractDomainObject
{
    final public const SINGULAR_NAME = 'password_reset';
    final public const PLURAL_NAME = 'password_resets';
    final public const EMAIL = 'email';
    final public const TOKEN = 'token';
    final public const CREATED_AT = 'created_at';
    final public const UPDATED_AT = 'updated_at';
    final public const DELETED_AT = 'deleted_at';
    final public const ID = 'id';
    final public const IP_ADDRESS = 'ip_address';
    final public const USER_AGENT = 'user_agent';

    protected ?string $email = null;
    protected ?string $token = null;
    protected ?string $created_at = null;
    protected ?string $updated_at = null;
    protected ?string $deleted_at = null;
    protected int $id;
    protected string $ip_address;
    protected ?string $user_agent = null;

    public function toArray(): array
    {
        return [
                    'email' => $this->email ?? null,
                    'token' => $this->token ?? null,
                    'created_at' => $this->created_at ?? null,
                    'updated_at' => $this->updated_at ?? null,
                    'deleted_at' => $this->deleted_at ?? null,
                    'id' => $this->id ?? null,
                    'ip_address' => $this->ip_address ?? null,
                    'user_agent' => $this->user_agent ?? null,
                ];
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
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

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setIpAddress(string $ip_address): self
    {
        $this->ip_address = $ip_address;
        return $this;
    }

    public function getIpAddress(): string
    {
        return $this->ip_address;
    }

    public function setUserAgent(?string $user_agent): self
    {
        $this->user_agent = $user_agent;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->user_agent;
    }
}
